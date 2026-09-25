# Two Redis instances for cache and sessions

Cache and sessions run in two Redis instances, not in one instance with two
databases.\
`maxmemory` and its eviction policy apply to the whole process, so a single
instance cannot evict cache entries under memory pressure and never evict
sessions.\
The cache instance evicts with `allkeys-lru` and persists nothing; the session
instance never evicts and persists with AOF.
