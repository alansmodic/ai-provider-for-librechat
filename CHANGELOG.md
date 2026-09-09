# Changelog

All notable changes to this project are documented in this file.

## [Unreleased]

### Changed
- Align PHPCS with WordPress VIP Go plus WordPress Core, Extra, and Docs.
- Treat LibreChat as a `server` provider (self-hosted), not a cloud provider.
- Sanitize instance URLs to `http`/`https` and API keys before use.
- Point the plugin Settings link at a dedicated instance URL screen.

### Added
- Settings > LibreChat screen for the instance URL, with Settings API sanitization.
- Admin notice when the WordPress AI Client is not available.
- Uninstall handler that deletes the instance URL option.
- GitHub Actions workflow to run PHPCS.

## [1.0.0]

### Added
- LibreChat provider registration for the WordPress AI Client.
- Text generation by invoking LibreChat agents through the OpenAI-compatible
  `/api/agents/v1/chat/completions` endpoint, including multi-turn conversations and token usage.
- Live agent discovery via `/api/agents/v1/models`, presenting each agent as a selectable model.
- `ai_provider_for_librechat_supported_options` filter for advertising per-request options once
  confirmed against an instance.
