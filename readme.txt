=== AI Provider for LibreChat ===
Contributors: alansmodic
Tags: ai, librechat, self-hosted, agents, llm
Requires at least: 7.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

LibreChat provider for the WordPress AI Client. Use agents from a self-hosted LibreChat instance.

== Description ==

**PROTOTYPE targeting a BETA API.** Not affiliated with or endorsed by the LibreChat project.
LibreChat states that the Agents API may change as it moves toward a stable release, so this plugin
should be expected to break. Validate against your own instance before deploying.

Registers LibreChat as a provider for the WordPress AI Client, so any AI-aware plugin, block or
Ability can use agents from a self-hosted LibreChat instance with no LibreChat-specific code.

LibreChat fronts many providers, so one connector reaches whatever an organization has configured
behind it. Because the Agents API presents agents as models, an agent's system prompt, tools and
attached files stay defined and governed inside LibreChat rather than in the CMS.

= Configuration =

Enable `remoteAgents.use` and `remoteAgents.create` in librechat.yaml, then create an agent and
generate an API key in the LibreChat UI.

In WordPress, set:

1. `LIBRECHAT_BASE_URL` — your instance URL (constant or environment variable)
2. `LIBRECHAT_API_KEY` — environment variable, PHP constant, or Settings > Connectors

== Frequently Asked Questions ==

= Why can I not set temperature or max tokens? =

Agents own their configuration inside LibreChat, and the Agents API does not document whether
per-request overrides are honored. Those options are therefore not advertised by default. Use the
`ai_provider_for_librechat_supported_options` filter to enable them once confirmed.

= Can I use a base model like GPT-4o directly? =

Not through this API. The Agents API exposes agents, so create an agent for the model you want.

== Changelog ==

= 1.0.0 =
* Initial release.
