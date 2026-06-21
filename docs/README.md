# Docs library

This folder is the **file-based knowledge base** that the `file_search` agent tool
reads from. There is **no database and no embeddings** involved — when an agent calls
`file_search`, the files below are read from disk and searched lexically on the fly.

## Convention

- Each sub-folder is a **collection** (e.g. `spaces/`, `policies/`, `tenants/`).
- Drop `.md`, `.markdown`, or `.txt` files into a collection.
- An agent can be scoped to specific collections, so it only ever reads the files
  it is allowed to see.

To add knowledge, just add a file — nothing to migrate, index, or re-seed.
