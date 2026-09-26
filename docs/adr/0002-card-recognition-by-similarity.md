---
status: accepted
---

# Recognise cards by image similarity, without training

A scan compares the photo with every catalogue image and returns the 3 most
similar cards.\
A pretrained model (DINOv2) turns each image into a fingerprint, and a search
library (FAISS) finds the closest fingerprints.\
Nothing is trained on our cards: a new extension only needs its images, one per
card.\
It works in manual tests, but its accuracy on real phone photos is not measured
on a dataset yet.

## Considered options

- A classifier trained on the catalogue: needs many photos per card and a new
  training run for every extension.

## Consequences

- The evaluation set (#86) sets the baseline; fine-tuning on augmented
  catalogue images is explored against it (#87).
- Adding an extension without rebuilding the whole index is still to be
  decided (#134).
