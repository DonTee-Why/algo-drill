from pathlib import Path


class TemplateLoader:
    def __init__(self, prompts_dir: Path | None = None):
        self.prompts_dir = prompts_dir or Path(__file__).resolve().parent.parent / "prompts"
        self._cache: dict[str, str] = {}

    def load(self, relative_path: str) -> str:
        if relative_path in self._cache:
            return self._cache[relative_path]

        path = self.prompts_dir / relative_path
        if not path.exists():
            raise FileNotFoundError(f"Prompt template not found: {relative_path}")

        content = path.read_text(encoding="utf-8").strip()
        if not content:
            raise ValueError(f"Prompt template is empty: {relative_path}")

        self._cache[relative_path] = content
        return content

    def try_load(self, relative_path: str) -> str | None:
        try:
            return self.load(relative_path)
        except (FileNotFoundError, ValueError):
            return None
