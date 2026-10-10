# Data

## redirects.csv

`status` values:

- `needs-review`: destination not settled.
- `proposed`: destination agreed in principle.
- `approved`: reviewed and ready to ship with the destination section's release.
- `live`: imported into Redirection on production.

`uv run scripts/release_redirects.py /<section>/` outputs only `approved` rows whose destination page is `ready-production` or `published`.
