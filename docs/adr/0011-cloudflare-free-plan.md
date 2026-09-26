---
status: accepted
---

# Put Cloudflare in front, on the free plan, without Bot Fight Mode

Visitors reach prod and preprod through Cloudflare's proxy, on the free plan,
for DNS and protection against traffic floods; caching comes later, once
traffic is observed (#125).\
Its Bot Fight Mode stays off: on the free plan no path can be exempted, and a
bot check on `/api/*` would send the frontend an HTML page where it expects
JSON, which breaks the app.\
Turnstile (#38) protects the forms that bots target instead.

## Consequences

- Symfony trusts Cloudflare's IP ranges to read the visitor's real IP (#123);
  without it, rate limiting (#24, #41) would throttle everyone at once.
- Cloudflare is a sub-processor in the processing register (#82).
- HSTS is enabled only at the prod launch (#69), since it is hard to undo.
