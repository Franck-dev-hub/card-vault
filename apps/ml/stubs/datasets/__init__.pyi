from typing import Any, Iterator

class Dataset:
    def __init__(self, *args: Any, **kwargs: Any) -> None: ...
    
    @staticmethod
    def from_generator(
        generator: Iterator[dict[str, Any]],
        features: Any = None,
        *args: Any,
        **kwargs: Any
    ) -> Dataset: ...
    
    def push_to_hub(
        self,
        repo_id: str,
        *args: Any,
        **kwargs: Any
    ) -> None: ...
    
    def iter(self, batch_size: int = 1, *args: Any, **kwargs: Any) -> Iterator[dict[str, Any]]: ...


class Features:
    def __init__(self, schema: dict[str, Any]) -> None: ...


class Image: ...


class Value:
    def __init__(self, dtype: str) -> None: ...


def load_dataset(
    path: str,
    *args: Any,
    **kwargs: Any
) -> Dataset: ...
