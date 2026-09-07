# Releasing Access Control

Follow the [Package release standard](package-release-standard.md) for the shared
quality gate, changelog parsing, publication and retry behavior. Complete the
[repository release setup](repository-release-setup.md) with an administrator
session before merging a release record:

```bash
bash tools/configure-release-repositories.sh --check kumwe/access-control
bash tools/configure-release-repositories.sh --apply kumwe/access-control
```

The required CI check is **Package gate**. Maintainers rebase reviewed PRs into the
repository's current default branch; the release workflow reruns the same quality
gate on the resulting commit and derives its release identity from that run.
A release intention in CHANGELOG.md is not evidence that publication occurred.
Keep work that is not ready for publication under `## Unreleased`.

## Upstream release gate

Access Control requires an independently verified immutable Access Context release.
Follow [dependency release verification](dependency-release-gate.md): select a
verified exact successor, attach authentic external evidence by digest, update the
Composer/handoff/manifests/consumer tuple, and rerun every check. The live dependency
gate resolves the selected version and validates publication and evidence before
tag mutation. Passing package CI alone does not satisfy this prerequisite.

Preserve unresolved evidence until real upstream releases and independent
verification exist. The package's own post-publication attestation and shipped
handoff remain prerequisites for SDK or App adoption.

## Publication evidence and recovery

The maintainer performs the initial Packagist submission. Its GitHub integration
then follows tags without a registry credential in CI. Before dependent publication
or App adoption, a fresh independent verifier must bind the exact published
source/tag, archive digest, manifests, registry coordinate, license/security and
clean-consumer results in an external RELEASE-ATTESTATION.yaml. The artifact and
handoff must not invent their own final commit, checksum or publication evidence.

Use the current release workflow on the default branch to retry after correcting
repository settings. Historical mutable releases remain unchanged: enabling
immutability affects future publications, so a mutable version requires an unused
successor. Never move or delete a published tag or replace a released artifact.
An unpublished tag can be completed only on the exact commit tested by the retry.
A green PR does not replace the default-branch release result or independent
verification. Administrator credentials do not belong in Actions.
