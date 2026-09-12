# Dependency contract

The runtime requires exact `kumwe/access-context 0.1.2` and `psr/container ^2.0`. Composer metadata is authoritative;
release-readiness coordinates and the consumer archive gate must agree with the selected graph.

`composer dependencies:check` rejects stale, missing, duplicate or mismatched dependency evidence entries.
Normal publication verifies stable dependency release/tag and Composer source/dist identities. Independent
verification additionally binds source/archive/manifest and clean-consumer evidence before Core or SDK adoption.
See [dependency release verification](dependency-release-gate.md) for both checks.

Consumers select compatible exact Kumwe versions together, resolve their lockfile and validate actual integration
suites. Existing published tags and archives remain unchanged. A recorded version alone does not establish passing
external release attestation or successful consumer integration.
