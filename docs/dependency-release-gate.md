# Dependency release verification

The release job runs `bash tools/check-release-dependencies.sh` after a production Composer install and before
creating a tag. It verifies live upstream publication and independent evidence. Source **Package gate** runs
the package checks and release automation regressions, including this checker's offline fixtures. Live upstream
evidence is a publication prerequisite and does not determine whether proposed source changes pass their tests.

Version 2 distinguishes `package-implemented`, a complete green implementation PR, from `package-released`, an
observed publication, and `release-verified`, a published artifact independently verified with a clean consumer.
Only verified upstream releases permit this package's publication. A passing source gate alone does not prove
that its selected dependency has reached that state.

The checker discovers every resolved Kumwe runtime package from `composer.lock`; direct Kumwe requirements in
`composer.json` must be exact stable versions. Development-only dependencies are excluded.

For each package, the gate reads GitHub's current release, tag and workflow records. Publication must be stable
and immutable. A lightweight or annotated version tag must resolve to the same full commit as Composer's source
and dist references. The external attestation must verify that exact version, tag, commit and dist URL, contain
source/archive and manifest digests, record independent registry and clean-consumer verification, and report no
known gaps. Its `release-on-record.yml` workflow must have completed successfully at the tagged commit. PR
head identities are never embedded in this gate: source identities come from the selected dependency release
at runtime.

`resources/release-readiness.json` provides reviewed evidence coordinates under `dependencies`, keyed by Composer
package name. Each entry carries the selected exact `version` and an `attestation` object with:

| Field | Required value |
| --- | --- |
| `repository` | A separate `kumwe/…` GitHub repository containing independently produced evidence |
| `commit` | The full 40-character commit containing that evidence |
| `path` | Repository-relative path ending in `.yaml`, `.yml`, or `.json` |
| `sha256` | SHA-256 digest of the exact evidence file bytes |

The evidence uses the existing `kumwe-release-attestation/v2` fields. The protocol's `RELEASE-ATTESTATION.yaml`
and equivalent `.yml` or `.json` files are accepted. The checker verifies the SHA-256 digest of the downloaded
file bytes before decoding; it requires exactly one object document and normalizes it to JSON internally.
YAML input requires Mike Farah `yq` version 4, available on the Ubuntu Actions runner. JSON input uses `jq`.
The complete regression suite also requires `yq` so YAML coverage cannot silently be skipped.

The `release_workflow` accepts the GitHub run URL, optionally followed by a parenthesized note. Format conversion
preserves all independent release, artifact, manifest and consumer evidence checks. The checker does not
generate an attestation or turn a successful local build into independent verification.

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
dependent publication remains blocked. Repository settings alone cannot update a dependency pin or supply its
independent verification. The release job checks the selected dependency evidence again on the actual merged
commit, after the complete source gate passes.

The publisher also verifies this repository's live default-branch protection. Complete the administrator setup
audit in `docs/repository-release-setup.md` for required checks and immutable-release configuration. The optional
`bash tools/configure-release-repositories.sh --apply --dispatch` verifies setup and requests default-branch
release runs. Dispatch does not establish dependency readiness or publication; inspect each run and its logs.
Declare the dependent package released only after its publication workflow succeeds and its immutable release
is present, then obtain its independent verification before downstream use.

Run `bash tools/test-release-dependencies.sh` for the isolated regression suite. Its fake GitHub responses prove
valid releases pass and mutable releases, stale source/dist identities, missing or changed evidence and failed
released-commit workflows fail. The fixtures make no network requests and do not alter published releases.
