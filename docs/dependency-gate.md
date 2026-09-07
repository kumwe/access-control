# Dependency state

Access Context 0.1.1 is published at `cb6aefd575401192b83700d2d06c739e2a39285f`; this successor requires that exact
stable version. Access Control 0.1.0 was published at `54dbaa1dbffeb09ba390a5e75e8adc951c437a41` with Context
0.1.0. Existing tags and release identities remain unchanged.

Publication checks the tested source, package gates and exact stable dependency tag/source/dist identity. The
separate strict audit and independent release attestation determine App adoption readiness. No passing external
attestation is asserted here.

The published Approval 0.1.0 graph still requires Access 0.1.0 and Context 0.1.0. Its successor must retain that
coherent graph until compatible Access and Audit successors have actually been published and verified, then update
all three exact requirements together through Composer. Do not substitute `latest`, `dev-main`, wildcard
requirements or a guessed future release.