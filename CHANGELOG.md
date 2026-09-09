# Changelog

All notable changes to this project are documented in this file.

## [1.0.0]

### Added
- LibreChat provider registration for the WordPress AI Client.
- Text generation by invoking LibreChat agents through the OpenAI-compatible
  `/api/agents/v1/chat/completions` endpoint, including multi-turn conversations and token usage.
- Live agent discovery via `/api/agents/v1/models`, presenting each agent as a selectable model.
- `ai_provider_for_librechat_supported_options` filter for advertising per-request options once
  confirmed against an instance.
