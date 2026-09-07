# Dependency publication and independent verification

## Normal publication identity check

After production Composer installation, the release job runs:

```bash
bash tools/check-package-dependencies.sh .
```

This check runs before creating the dependent package's tag. It discovers every resolved
Kumwe runtime package from `composer.lock`; direct Kumwe requirements in `composer.json`
must use exact stable versions. Development-only dependencies are excluded.

Each selected runtime dependency must have a published stable GitHub release. Its
lightweight or annotated version tag must resolve to the exact full commit recorded in
Composer's source and dist references. The checker validates the release coordinate and
source/archive identities against the selected package. Missing releases, unstable
versions, mismatched tags or source/dist identities and failed API reads block publication.
It does not require GitHub's optional immutable-release flag or an external attestation.

Source **Package gate** continues to run the complete package checks and release
regressions, including the dependency identity fixtures. The release workflow repeats
that gate after rebase and checks live dependency identities on the event's exact commit.
PR head SHAs are never embedded as future dependency or package release identities.

Version 2 distinguishes `package-implemented` from observed `package-released` and
independently established `release-verified`. Passing the publication check does not
create independent evidence or resolve historical readiness gaps. Preserve the shipped
handoff and obtain the separate verification needed for SDK or App adoption.

## Optional strict evidence audit

`tools/check-release-dependencies.sh` remains available for a separate strict audit:

```bash
bash tools/check-release-dependencies.sh . "$RUNNER_TEMP/dependency-verification.json"
```

This audit is not called by the normal publisher. In addition to stable publication and
tag/source/dist identity, it requires a platform-immutable upstream release, a successful
`release-on-record.yml` workflow at the tagged commit and independent external evidence.
The attestation must bind the exact version, tag, commit and dist URL, contain source/archive
and manifest digests, record registry and clean-consumer verification, and report no
known gaps. A mutable release cannot satisfy this stricter audit.

`resources/release-readiness.json` provides reviewed evidence coordinates under
`dependencies`, keyed by Composer package name. Each entry has the selected exact
`version` and an `attestation` object:

| Field | Required value |
| --- | --- |
| `repository` | A separate `kumwe/…` repository containing independently produced evidence |
| `commit` | The full 40-character commit containing that evidence |
| `path` | Repository-relative path ending in `.yaml`, `.yml`, or `.json` |
| `sha256` | SHA-256 digest of the exact evidence file bytes |

The audit accepts the existing `kumwe-release-attestation/v2` fields in
`RELEASE-ATTESTATION.yaml` or equivalent `.yml` and `.json` files. It verifies the downloaded
bytes' SHA-256 digest before decoding, requires one object document and normalizes it to
JSON. YAML needs Mike Farah `yq` version 4; JSON uses `jq`. The complete strict-audit
regression suite requires `yq` so YAML coverage cannot silently be skipped.

`release_workflow` accepts the GitHub run URL, optionally followed by a parenthesized
note. The audit never generates an attestation or treats a local build as independent
verification. A historical top-level `status` neither grants nor refuses permission by
itself. Missing coordinates, null attestations, stale digests, differing selected versions,
mutable releases and failed observations produce specific audit failures.

Successful auditing does not rewrite the historical readiness file. Its optional second
argument writes a fresh machine-readable result and invalidates an earlier passing result
before verification begins. These strict audit results support `release-verified` and
adoption decisions separately from normal publication.

## Updating dependencies and reporting evidence

For normal publication, select an exact published stable version, update Composer and
the matching dependency versions in manifests, handoff and consumer checks, resolve again,
and run the full source gate and publication identity check. Preserve runtime behavior and
clean no-dev registry/archive consumer verification; do not substitute a path repository.

For a strict audit requiring platform immutability, an existing mutable release remains
mutable after repository settings change. Use an unused immutable successor when needed;
never move a released tag or replace its artifact. Have an independent verifier inspect
the exact source, Composer archive, manifests, registry metadata and clean consumer, then
record the authentic attestation and digest. Normal publication does not invent that evidence.

[Repository hardening](repository-release-setup.md) is optional. Its administrator helper
can audit or apply branch rules and future immutable publication when explicitly invoked;
it is not a publisher prerequisite and does not supply dependency evidence. Existing
GitHub rules and permissions remain effective. A queued workflow does not prove a release;
confirm the successful publication run and actual release/tag metadata. Describe a release
as platform-immutable only when GitHub confirms it, and obtain independent evidence before
claiming `release-verified` or downstream adoption readiness.

The dependency identity fixtures and `bash tools/test-release-dependencies.sh` strict-audit
fixtures run offline with fake GitHub responses. Keep both aligned with their respective
checks; neither changes published releases or establishes live independent evidence.
