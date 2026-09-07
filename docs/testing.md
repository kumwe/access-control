# Package test ownership

Every public export is mapped to discovered behavior and boundary test IDs in tests/ownership.json. composer
test:ownership checks the complete API set, actual discovered test IDs, conformance corpus and retained host
responsibilities; its negative fixtures reject omitted/unmapped exports and invented tests. Future exports cannot pass
CI without ownership evidence. This gate proves inventory; reviewers still assess assertion quality.

Decision algebra corpus, exact scope matching, denial/reason vocabulary, identifier grammar, owner
collision/delegation/lifecycle, sorted removal, registry replacement, hostile definitions/iterables, explicit
configuration and API boundary tests run here. PortTest supplies contract protocol fixtures for host adapter
conformance. These fixtures are test doubles, not deployable authority/persistence implementations, and do not replace
host DB/security tests.

Remove on verified App adoption: GrantScopeTest, the two old decision model behavior tests,
ResourcePolicyDefinitionTest and ResourcePolicyRegistryTest. Split OwnershipScopeModelTest (portable containment/rule
mechanisms here, exact App 44category security table in App) and AuthorizationPolicyRegistryTest (generic registry
here, actual sensitive target configuration in App). SDK Capability generic unit behavior belongs here after its
separate successor adoption.

Retain App authorization adapter parity, vertical/horizontal escalation, query/count/relation/report leakage,
membership/role staleness, token audience, trust revocation, direct invocation, actual DB ownership/CAS, audit
failures, lifecycle and recovery suites. Do not remove App tests until their runtime owner is replaced by the exact
verified package. Do not attribute App unit coverage to vendor implementation classes.

Run composer check. The archive gate builds a ZIP, installs it as a real dependency with --no-dev
--classmap-authoritative and no plugins/scripts, compares shipped bytes and public classmap, and runs its example.
Runtime dependencies resolve from their exact published coordinates; this is installation verification, not the
independent release attestation.
