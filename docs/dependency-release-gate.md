# Dependency release verification

The release job runs `bash tools/check-release-dependencies.sh` after Composer resolves dependencies and before
creating a tag. The checker discovers every resolved Kumwe runtime package from `composer.lock`; direct Kumwe
requirements in `composer.json` must be exact stable versions. Development-only dependencies are excluded.

For each package, the gate reads GitHub's current release, tag and workflow records. Publication must be stable
and immutable. A lightweight or annotated version tag must resolve to the same full commit as Composer's source
and dist references. The external attestation must verify that exact version, tag, commit and dist URL, contain
source/archive and manifest digests, record independent registry and clean-consumer verification, and report no
known gaps. Its `release-on-record.yml` workflow must have completed successfully at the tagged commit. PR
head identities are
never embedded in this gate: all source identities come from the selected dependency release at runtime.

`resources/release-readiness.json` provides reviewed evidence coordinates under `dependencies`, keyed by Composer
package name. Each entry carries the selected exact `version` and an `attestation` object with:

| Field | Required value |
| --- | --- |
| `repository` | A separate `kumwe/…` GitHub repository containing independently produced evidence |
| `commit` | The full 40-character commit containing that evidence |
| `path` | Repository-relative path ending in `.json` |
| `sha256` | SHA-256 digest of the exact evidence file bytes |

The evidence uses the existing `kumwe-release-attestation/v2` fields in JSON serialization, which is also valid
YAML. Its `release_workflow` accepts the GitHub run URL, optionally followed by a parenthesized note. JSON avoids
introducing a YAML interpreter into this Bash/jq release gate. A digest binds the complete independent record;
the checker does not generate an attestation or turn a successful local build into independent verification.

A historical top-level `status` does not grant or refuse permission by itself. Missing coordinates, null
attestations, stale digests, different selected versions, mutable releases, and failed observations all fail
closed with a specific diagnostic. Successful verification does not rewrite the historical readiness file.
An optional second argument writes a fresh machine-readable verification result, invalidating any earlier
passing result before verification begins:

```bash
bash tools/check-release-dependencies.sh . "$RUNNER_TEMP/dependency-verification.json"
```

To clear a blocked dependency, first publish a new immutable successor when the existing release is mutable;
never move a released tag or treat enabling immutability as proof that an old release became immutable. Have
an independent verifier inspect that exact successor, its source and Composer archives, manifests, registry
metadata and isolated no-dev consumer, and commit its authentic external attestation. Then update the exact
Composer pin, the matching evidence coordinates, and any dependency version recorded in manifests, handoff
or checks. Resolve Composer again and run the package gates and this live checker. Until these facts exist,
the dependent package remains blocked; the fix does not invent a successor version or evidence.

Run `bash tools/test-release-dependencies.sh` for the isolated regression suite. Its fake GitHub responses prove
valid releases pass and mutable releases, stale source/dist identities, missing or changed evidence and failed
released-commit workflows fail. The fixtures make no network requests and do not alter published releases.
