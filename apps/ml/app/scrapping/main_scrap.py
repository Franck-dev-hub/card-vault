from pathlib import Path
from concurrent.futures import ThreadPoolExecutor, as_completed
from . import pokemon_scrap as pokemon_manager
# from . import magic_scrap as magic_manager

OUTPUT_DIR = Path("images")
OUTPUT_DIR.mkdir(exist_ok=True)
MAX_WORKERS = 5
MANAGERS = [
    pokemon_manager,
    # magic_manager
]


def main():
    tasks = []
    stats = {}

    # Loop through all managers
    for manager in MANAGERS:
        try:
            cards = manager.fetch_all_cards()
            stats[manager.__name__] = len(cards)
            for card in cards:
                tasks.append((card, manager.download_card))
        except Exception as e:
            print(f"Failed to load manager {manager.__name__}: {e}")

    total_tasks = len(tasks)
    if total_tasks == 0:
        print("No cards found or API error.")
        return

    breakdown = ", ".join(
        [f"{name}: {count}" for name, count in stats.items()]
    )
    print(f"Total cards: {total_tasks} ({breakdown})\n")

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
                error_count += 1
                errors_detail[f"Task_{index}"] = str(e)

            # Display progression
            progress = success_count + error_count
            percentage = (progress / total_tasks) * 100
            print(
                f"Progression: {progress}/{total_tasks}({percentage:.1f}%)",
                end="\r",
            )

    # Result summary
    print("\n" + "=" * 50)
    print(f"Total processed => {total_tasks}")
    print(f" - Success ------> {success_count}")
    print(f" - Downloaded ---> {success_count - skipped_count}")
    print(f" - Skipped ------> {skipped_count}")
    print(f" - Errors -------> {error_count}")
    print("=" * 50)


if __name__ == "__main__":
    main()
