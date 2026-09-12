.PHONY: release/preprod release/prod

# === RELEASES ===
release/preprod:
	gh pr create --base preprod --head develop --title "release: develop -> preprod" --fill

release/prod:
	gh pr create --base prod --head preprod --title "release: preprod -> prod" --fill
