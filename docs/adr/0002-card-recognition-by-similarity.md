---
status: accepted
---

# Recognise cards by image similarity, without training

A scan turns the photo into an embedding with the pretrained DINOv2 model, then returns the nearest cards from a FAISS index of the catalogue images.\
Nothing is trained: adding an extension means embedding its images, and one reference image per card is enough.\
It was built for a demo, and its accuracy on real phone photos is not measured yet; revisit this decision once an evaluation set exists.

## Considered options

- A classifier trained on the catalogue: needs many photos per card and a new training run for every extension.
