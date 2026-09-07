# Release gate

This draft deliberately exits before any tag/release mutation: Access Context 0.1.0 lacks an independent passing
immutable-release attestation. First select a verified exact successor, attach authentic external evidence by digest,
update the Composer/handoff/manifests/consumer tuple and rerun every check. Do not remove this failure merely because
CI passes.

Once that prerequisite is reviewed, main must be protected and GitHub immutable releases enabled before the human
merge. Release-on-record runs package gates, verifies protected main, creates one new version tag and verifies the
published release reports immutable:true. Never move/delete a published tag or replace an artifact. Initial Packagist
submission is a maintainer action. An independent fresh verification session binds source, archive, manifests,
registry and no-dev consumer evidence; only its external passing attestation permits SDK/App adoption.
