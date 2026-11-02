# Casino API Client

WordPress plugin with a Gutenberg block that displays casino cards from a REST API.

## Requirements
* WordPress 6.7 or newer
* PHP 8.1 or newer

## Installation
Copy the plugin to `wp-content/plugins`, or upload it as a ZIP, then activate it. The folder name does not matter.

## Configuration
In **Settings > Casino API Client**, insert the API base URL, username and password.

The plugin requests `GET <base URL>/casinos` with Basic Authentication. The response must be a JSON array of casinos with
the fields `id`, `name`, `logo_url`, `average_rtp`, `biggest_win_month`, `payment_delay_hours`,
`monthly_withdrawal_limit`, `validated_withdrawals_value` and `monthly_withdrawals_number`. Rows without these fields
are skipped.

## Usage
1. Add the **Casino Card** block.
2. Select the casino.
3. Insert the affiliate link in the block settings. Without a link, the card has no call to action.

## Development
Node 18 and npm 9 or newer:

```bash
nvm use
npm ci
npm run build
```

`build/` is committed, so the plugin works without a Node toolchain. The block lives in `src/casino-card`, the data
access in `includes`.

## Impact Analysis
### Performance
The card adds one stylesheet and no front-end JavaScript. The API is requested at most once per 6 hours, or once per
5 minutes while it is failing, with a 5-second timeout. A large logo can become the page's LCP element, so serve logos
at their display size.

### Plugin Conflicts
All styles are scoped to `.casino-api-card`. The block uses the core `widgets` category and registers one REST route
under `casino-api-client/v1`.

### Security
Card output is escaped. The REST route requires the `edit_posts` capability and returns only casino ids and names.
The API password is stored in the WordPress options table and is never printed back. Uninstalling the plugin removes
it.

### Cache Impact
Saving the settings clears the plugin's cache. Page caches keep a rendered card for their own lifetime, so a selected
casino's numbers can be older than 6 hours there.

## License
GPL-2.0-or-later, see [LICENSE](LICENSE).
