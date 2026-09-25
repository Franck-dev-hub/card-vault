# Authenticate with a session cookie, not a JWT

Users log in to a server-side session, stored in Redis and carried by a cookie.\
A session can be revoked at once by deleting it on the server, while a JWT stays
valid until it expires.\
The frontend and the API share one origin (see
[0003](0003-internal-ml-single-origin.md)), so the cookie needs no cross-origin
setup.
