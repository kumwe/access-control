# Kumwe Access Control

Own portable authorization decisions, capability and resource-policy definitions, deterministic owner-bound
registries, grant/ownership scopes and explicit host authority ports under `Kumwe\Access\`.

The package does not authenticate, issue principals, activate contributions, validate trust, look up
sessions/tokens/users/roles, persist, transact, audit, deliver responses or grant authority by itself. Context is
supplied explicitly through `kumwe/access-context`; PSR Container is used only by factories. Framework configuration
constructs package services but contains no reserved App policy or automatically active definitions.

Behavior, boundary, API and port conformance tests belong here. App owns composition, host authority, database,
delivery, lifecycle, trust and recovery tests. The source map and handoff govern removal during a later verified
adoption; no App source is changed by this draft.
