# sitewatch

A tiny, dependency-free command-line tool that checks whether your website is
up, how fast it responds, and (optionally) whether its links are broken.

**Read-only by design:** sitewatch only sends HTTP `GET` requests. It never
modifies, deploys to, or writes anything on the site it checks. This repository
is completely separate from your website's code.

## Features

- Check one or many URLs for status code and response time
- Load URLs from a JSON config file
- Non-zero exit code when a check fails (handy for cron / CI)
- Broken-link scanning for a page (see `docs/link-checking.md`)
- Standard library only, Python 3.9+

## Quick start

```bash
git clone https://github.com/YOUR-USERNAME/sitewatch.git
cd sitewatch
python -m sitewatch check https://example.com
```

Using a config file:

```bash
cp config.example.json config.json   # edit with your URLs
python -m sitewatch check --config config.json
```

## Running tests

```bash
python -m unittest discover -s tests -v
```

## Documentation

- [Usage](docs/usage.md)
- [Architecture](docs/architecture.md)

## License

MIT
