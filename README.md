# AI Provider for LibreChat

> [!WARNING]
> **This is a prototype, and it targets a beta API.** It is not production software and is not
> affiliated with or endorsed by the LibreChat project. LibreChat states that the Agents API's
> "endpoints, request/response formats, and behavior may change as we iterate toward a stable
> release" — so this plugin should be expected to break. Review and test against your own instance
> before deploying.

LibreChat provider for the [WordPress AI Client](https://make.wordpress.org/core/2026/03/24/introducing-the-ai-client-in-wordpress-7-0/). Lets a WordPress site use agents from a self-hosted [LibreChat](https://www.librechat.ai/) instance.

Structured to match the official provider plugins and [Fueled's Ollama provider](https://github.com/Fueled/ai-provider-for-ollama).

## Verification status

Checked against a live LibreChat **v0.8.8-rc2** instance (Docker), not only against documentation:

| Checked | Result |
|---|---|
| `POST /api/agents/v1/chat/completions` mounted | confirmed in route source |
| `GET /api/agents/v1/models` mounted | confirmed in route source |
| Bearer scheme | server replies `Expected: Bearer <api_key>` |
| `remoteAgents` feature gate | confirmed (`checkRemoteAgentsFeature` middleware) |
| Response bodies with a valid key | **not verified** — keys are issued through the LibreChat UI |

So the URLs and authentication are verified against a real server. The response parsing relies on
the OpenAI-compatible format, handled by the AI Client SDK.

## Why route through LibreChat

LibreChat fronts many providers — OpenAI, Anthropic, Bedrock, Ollama, local models — so a single
connector reaches whatever an organization has configured behind it, with their existing
authentication, logging and quotas applied.

More importantly, **LibreChat's Agents API presents agents as models.** An agent carries its own
system prompt, tools, attached files and MCP servers, all defined and governed inside LibreChat. An
administrator builds "Newsroom Style Editor" once; it then appears in WordPress as a selectable
model. WordPress never constructs the prompt or picks the underlying model.

For teams that self-host — including on private or air-gapped networks — this keeps prompt policy
in one reviewable place, outside the CMS.

## Install

```bash
composer require alansmodic/ai-provider-for-librechat
```

Or drop the directory into `wp-content/plugins/` and activate — a fallback PSR-4 autoloader is included, so `composer install` is optional.

## Development

```bash
composer install
composer phpunit
composer phpcs
```

### LibreChat side

Enable the Agents API in `librechat.yaml`:

```yaml
remoteAgents:
  use: true
  create: true
```

Then create an agent and generate an API key from the LibreChat UI.

### WordPress side

In **Settings > LibreChat**, set your instance URL. Store the API key in **Settings > Connectors**, or:

```php
// wp-config.php — these override the settings screen when present
define( 'LIBRECHAT_BASE_URL', 'https://your-librechat-instance' );
define( 'LIBRECHAT_API_KEY', getenv( 'LIBRECHAT_API_KEY' ) );
```

There is deliberately **no default base URL** — LibreChat is self-hosted, so without an instance URL
the provider reports itself unconfigured rather than guessing.

## Usage

No LibreChat-specific code is required:

```php
use WordPress\AiClient\AiClient;

$text = AiClient::prompt( 'Rewrite this in house style.' )->generateText();
```

Each discovered agent appears as a model, so a specific agent can be requested by its ID.

## Architecture

| Class | Extends | Role |
|---|---|---|
| `Provider\LibreChatProvider` | `AbstractApiProvider` | Provider registration |
| `Models\LibreChatTextGenerationModel` | `AbstractOpenAiCompatibleTextGenerationModel` | `/chat/completions` |
| `Metadata\LibreChatModelMetadataDirectory` | `AbstractOpenAiCompatibleModelMetadataDirectory` | Agent discovery via `/models` |

Because the Agents API implements the OpenAI Chat Completion format and uses a standard bearer
token, the SDK does nearly all the work: the model supplies only `createRequest()`, and the SDK's
own `ApiKeyRequestAuthentication` is used unchanged.

## Option support

Advertised: `inputModalities`, `outputModalities`, `customOptions`.

Sampling options such as `temperature` and `maxTokens` are **not advertised by default**. An agent
owns its own configuration inside LibreChat, and the Agents API documentation does not state
whether per-request overrides are honored. Advertising them would risk silently ignoring a caller's
settings. Enable them once confirmed against your instance:

```php
add_filter( 'ai_provider_for_librechat_supported_options', function ( $options ) {
    $options[] = new \WordPress\AiClient\Providers\Models\DTO\SupportedOption(
        \WordPress\AiClient\Providers\Models\Enums\OptionEnum::temperature()
    );
    return $options;
} );
```

Tool calling is likewise unadvertised: agents invoke their own tools server-side rather than
returning function calls for the caller to execute.

## Limitations

- **Agents, not raw models.** The Agents API exposes agents. To reach a base model directly, create
  an agent for it in LibreChat.
- **Beta API.** Expect breaking changes.
- **Streaming** is supported by LibreChat but not implemented here; the AI Client needs the separate
  `generateTextOperation()` interface for that.

## Notes

The bundled logo is a neutral placeholder, not the LibreChat project's mark.

## License

GPL-2.0-or-later
