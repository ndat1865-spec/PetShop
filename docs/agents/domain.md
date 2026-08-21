# Domain Docs

This is a single-context repository. Engineering skills should read the following before exploring relevant code:

- `CONTEXT.md` at the repository root, if it exists.
- Relevant decisions under `docs/adr/`, if they exist.

If these files do not exist, proceed silently. The domain-modeling skill creates them lazily when terms or decisions are resolved.

Use the vocabulary defined in `CONTEXT.md` when naming domain concepts in issues, refactors, and tests. If an output contradicts an existing ADR, surface the conflict explicitly instead of silently overriding it.

## File structure

```text
/
├── CONTEXT.md
├── docs/adr/
└── src/
```
