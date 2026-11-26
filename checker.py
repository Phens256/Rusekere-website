"""Core URL checking logic. Only performs HTTP GET requests."""
import time
import urllib.error
import urllib.request
from dataclasses import dataclass
from typing import Optional

USER_AGENT = "sitewatch/0.1 (read-only health check)"


@dataclass
class CheckResult:
    url: str
    status: Optional[int]
    elapsed_ms: float
    error: Optional[str] = None

    @property
    def ok(self) -> bool:
        return self.error is None and self.status is not None and 200 <= self.status < 400


def check_url(url: str, timeout: float = 10.0) -> CheckResult:
    """GET a URL and report status and response time. Never raises on network errors."""
    req = urllib.request.Request(url, method="GET", headers={"User-Agent": USER_AGENT})
    start = time.perf_counter()
    try:
        with urllib.request.urlopen(req, timeout=timeout) as resp:
            resp.read(1024)  # only read a small chunk; we just need the status
            status = resp.status
        error = None
    except urllib.error.HTTPError as exc:
        status, error = exc.code, None
    except Exception as exc:  # DNS failure, timeout, bad URL, etc.
        status, error = None, f"{type(exc).__name__}: {exc}"
    elapsed = (time.perf_counter() - start) * 1000
    return CheckResult(url=url, status=status, elapsed_ms=round(elapsed, 1), error=error)
