---
status: accepted
---

# Two Redis instances for cache and sessions

Cache and sessions live in two separate Redis instances.\
When memory is full, Redis applies one rule to everything it holds: drop old
entries, or refuse new ones.\
The cache must drop old entries; sessions must never be dropped, or users get
logged out.\
One instance cannot do both, so each gets its own.
