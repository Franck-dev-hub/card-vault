"""Download every card image to a local folder."""

import logging
from collections.abc import Callable
from concurrent.futures import ThreadPoolExecutor, as_completed
from pathlib import Path
from typing import Any

from app.log import setup_logging

from . import pokemon_scrap as pokemon_manager

OUTPUT_DIR = Path("images")
OUTPUT_DIR.mkdir(exist_ok=True)
MAX_WORKERS = 5
PROGRESS_EVERY = 100
MANAGERS = [pokemon_manager]

logger = logging.getLogger(__name__)

type Card = dict[str, Any]
type Downloader = Callable[[Card, Path], tuple[bool, str | None]]


def main() -> None:
    """Download the images of every managed game, in parallel."""
    tasks: list[tuple[Card, Downloader]] = []
    stats = {}

    # Loop through all managers
    for manager in MANAGERS:
        try:
            cards = manager.fetch_all_cards()
            stats[manager.__name__] = len(cards)
            tasks.extend((card, manager.download_card) for card in cards)
        except Exception:
            logger.exception("Failed to load manager %s", manager.__name__)

    total_tasks = len(tasks)
    if total_tasks == 0:
        logger.error("No cards found or API error")
        return

    breakdown = ", ".join(
        [f"{name}: {count}" for name, count in stats.items()]
    )
    logger.info("Total cards: %d (%s)", total_tasks, breakdown)

    success_count = 0
    skipped_count = 0
    error_count = 0
    errors_detail = {}

    with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
        # Submit tasks to thread pool
        futures = {
            executor.submit(download_func, card_data, OUTPUT_DIR): (
                card_data,
                i,
            )
            for i, (card_data, download_func) in enumerate(tasks)
        }

        # Process results as downloads complete and track stats/errors
        for completed_future in as_completed(futures):
            card_data, index = futures[completed_future]
            try:
                success, msg = completed_future.result()
                if success:
                    success_count += 1
                    if msg == "Skipped (Already exists)":
                        skipped_count += 1
                else:
                    error_count += 1
                    errors_detail[card_data.get("id", index)] = msg
            except Exception as e:
                logger.exception("Card %s crashed", index)
                error_count += 1
                errors_detail[f"Task_{index}"] = str(e)

            progress = success_count + error_count
            if progress % PROGRESS_EVERY == 0 or progress == total_tasks:
                logger.info("Progress: %d/%d", progress, total_tasks)

    logger.info(
        "Processed %d: %d downloaded, %d skipped, %d errors",
        total_tasks,
        success_count - skipped_count,
        skipped_count,
        error_count,
    )


if __name__ == "__main__":
    setup_logging()
    main()
