# Dependency release gate

This is an implemented draft, not an eligible package release. Executable development checks use exact published `kumwe/access-context 0.1.0` (source `34241cbd0cc67934536d2921eca14b063be6fb81`). It failed independent release verification because publication was mutable. The merged 0.1.1 release record is not a published dependency. No external passing release attestation exists in this draft.

The release workflow refuses publication while this gate remains unresolved. A separate commit must select the exact independently verified successor, include its authentic external evidence by digest, update the handoff and rerun all gates. Merely enabling repository settings, installing the draft, or passing CI does not establish release verification. App and SDK adoption wait.
