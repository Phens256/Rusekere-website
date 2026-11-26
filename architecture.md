# Architecture

```
sitewatch/
  checker.py   # check_url(): one GET request -> CheckResult
  cli.py       # argparse commands, config loading, output
  __main__.py  # enables `python -m sitewatch`
tests/         # unittest suite using a local throwaway HTTP server
```

- **checker.py** has no I/O besides the HTTP request, so it is easy to test.
- **cli.py** only wires arguments to checker functions and formats output.
- Tests run against a local server on `127.0.0.1`; they never touch real sites.
