"""Logging setup shared by the service and the scraping scripts."""

import logging

LOG_FORMAT = "%(asctime)s %(levelname)s %(name)s: %(message)s"


def setup_logging() -> None:
    """Log our INFO messages, and only the warnings of the libraries."""
    logging.basicConfig(format=LOG_FORMAT, level=logging.WARNING)
    # A script run with `python -m` logs as __main__, outside `app`.
    for name in ("app", "__main__"):
        logging.getLogger(name).setLevel(logging.INFO)
