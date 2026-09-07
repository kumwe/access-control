---
schema: kumwe-migration-handoff/v2
artifact_kind: "framework_php"
migration_id: "KUMWE-MIG-2026-009"
change_set: "KUMWE-CS-2026-009"
state: "draft_pr_open"
source:
  app:
    repository: "https://github.com/kumwe/app"
    baseline_commit: "960ce8ec00cf724a7cae03e5ba09c4852c9ab54e"
    examined_paths:
      - "src/Application/Authorization"
      - "src/Identity/Domain"
      - "tests/Unit/Application/Authorization"
      - "tests/Unit/Identity/Domain"
      - "vendor/kumwe/extension-sdk"
      - "composer.json"
      - "composer.lock"
      - "AGENTS.md"
    old_namespace_roots: []
    capability_index_sha256: "87ded886f35f74878ca9eb8db4c36e23d681c4a49891f76dfc3210f385a7ce39"
  semantic_inputs: []
  examined_dependencies:
    - "Access Context 0.1.0 source34241cbd0cc67934536d2921eca14b063be6fb81: exact executable development input, release-unverified."
    - "SDK0.2.4 sourced0484b8733eaa57d076f567ffa5e997b9564b5fa: source provenance only, no runtime SDK dependency."
    - "Conversion and Producer do not own authorization decisions, policies or scopes."
  active_related_pull_requests:
    - "https://github.com/kumwe/access-context/pull/3"
    - "https://github.com/kumwe/contribution/pull/2"
target:
  repository: "https://github.com/kumwe/access-control"
  artifact_identity: "kumwe/access-control"
  canonical_namespace_or_abi: "Kumwe\\Access"
  branch: codex/extraction-readiness-20260907
  pull_request: "https://github.com/kumwe/access-control/pull/4"
ownership:
  responsibility: "Reusable authorization decisions, policies, scopes, ownership, registries and ports."
  non_responsibilities:
    - "Principal/session/token/role authority, trust and active extension admission."
    - "Concrete host gateway, persistence, transactions, auditing and delivery."
    - "App sensitive resource categories and reserved ownership table."
  allowed_dependency_ceiling:
    - "kumwe/access-context"
  implementation_owner: "kumwe/access-control"
  next_consumer: "kumwe/extension-sdk"
  public_manifests:
    - path: resources/public-api/v1.json
      sha256: "cbe368cbf9bd9ba4a746aca08be1446f4d6c1b584944b9ba698917c6af40a502"
    - path: resources/capabilities/v1.json
      sha256: "69ab2fa6c8202f726ebdf5a54191aec6d89411bf617d656470435721e3284ec2"
    - path: resources/service-map/v1.json
      sha256: "05309b1f43fe60410b256d851c373e54dd83ae3456e6b65fbefb6efa850e57de"
  intentionally_excluded:
    - "Eight Context types already have another package owner."
    - "Seven App authorization types retain authority/orchestration."
    - "SystemIdentity closed enum and principal issuance remain App; portable policy holds neutral strings."
framework_php:
  composer_package: "kumwe/access-control"
  canonical_namespace: "Kumwe\\Access"
  public_api_manifest: "resources/public-api/v1.json"
  capability_manifest: "resources/capabilities/v1.json"
  service_map: "resources/service-map/v1.json"
  extracted_symbols:
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\AuthorizationDecision"
      new_fqcn: "Kumwe\\Access\\AuthorizationDecision"
      source_path: src/Application/Authorization/AuthorizationDecision.php
      target_path: src/AuthorizationDecision.php
      kind: "class"
      public_methods:
        - "__construct"
        - "toArray"
      public_properties:
        - "allowed"
        - "policy"
        - "reason"
        - "state"
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\AuthorizationDecisionRecorder"
      new_fqcn: "Kumwe\\Access\\AuthorizationDecisionRecorder"
      source_path: src/Application/Authorization/AuthorizationDecisionRecorder.php
      target_path: src/AuthorizationDecisionRecorder.php
      kind: "interface"
      public_methods:
        - "record"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\AuthorizationDefinitionLifecycle"
      new_fqcn: "Kumwe\\Access\\AuthorizationDefinitionLifecycle"
      source_path: src/Application/Authorization/AuthorizationDefinitionLifecycle.php
      target_path: src/AuthorizationDefinitionLifecycle.php
      kind: "enum"
      public_methods:
        - "enforceable"
      public_properties: []
      public_constants:
        - "Active"
        - "Deprecated"
        - "Disabled"
        - "Retired"
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\AuthorizationDenied"
      new_fqcn: "Kumwe\\Access\\AuthorizationDenied"
      source_path: src/Application/Authorization/AuthorizationDenied.php
      target_path: src/AuthorizationDenied.php
      kind: "class"
      public_methods:
        - "__construct"
      public_properties:
        - "action"
        - "policy"
        - "reason"
        - "resourceIdentifier"
        - "resourceType"
        - "siteIdentifier"
        - "subject"
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\AuthorizationGateway"
      new_fqcn: "Kumwe\\Access\\AuthorizationGateway"
      source_path: src/Application/Authorization/AuthorizationGateway.php
      target_path: src/AuthorizationGateway.php
      kind: "interface"
      public_methods:
        - "assertAllowed"
        - "assertCanDelegate"
        - "decide"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\AuthorizationPolicyRegistry"
      new_fqcn: "Kumwe\\Access\\AuthorizationPolicyRegistry"
      source_path: src/Application/Authorization/AuthorizationPolicyRegistry.php
      target_path: src/AuthorizationPolicyRegistry.php
      kind: "class"
      public_methods:
        - "__construct"
        - "allowsHumanGrant"
        - "allowsSystemIdentity"
        - "capability"
        - "capabilityDefinitions"
        - "registerCapability"
        - "registerResourcePolicy"
        - "removeOwner"
        - "requiresGlobalGrant"
        - "requiresMembershipContext"
        - "resourcePolicies"
        - "resourcePolicy"
        - "supports"
        - "supportsDelegation"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\AuthorizationResource"
      new_fqcn: "Kumwe\\Access\\AuthorizationResource"
      source_path: src/Application/Authorization/AuthorizationResource.php
      target_path: src/AuthorizationResource.php
      kind: "class"
      public_methods:
        - "collection"
        - "identifier"
        - "item"
        - "type"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\AuthorizationResourceOwnershipUnknown"
      new_fqcn: "Kumwe\\Access\\AuthorizationResourceOwnershipUnknown"
      source_path: src/Application/Authorization/AuthorizationResourceOwnershipUnknown.php
      target_path: src/AuthorizationResourceOwnershipUnknown.php
      kind: "class"
      public_methods:
        - "__construct"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\Extension\\Spi\\Identity\\Domain\\Capability"
      new_fqcn: "Kumwe\\Access\\Capability"
      source_path: vendor/kumwe/extension-sdk/src/Spi/Identity/Domain/Capability.php
      target_path: src/Capability.php
      kind: "class"
      public_methods:
        - "__toString"
        - "equals"
        - "fromString"
        - "value"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\CapabilityDefinition"
      new_fqcn: "Kumwe\\Access\\CapabilityDefinition"
      source_path: src/Application/Authorization/CapabilityDefinition.php
      target_path: src/CapabilityDefinition.php
      kind: "class"
      public_methods:
        - "__construct"
        - "allowsDelegation"
        - "allowsHumanGrant"
        - "assertOwnedIdentifier"
        - "assertOwner"
        - "enforceable"
        - "toArray"
      public_properties:
        - "allowedScopes"
        - "capability"
        - "definitionVersion"
        - "delegatable"
        - "highImpact"
        - "lifecycle"
        - "owner"
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\CapabilityDefinitionRegistry"
      new_fqcn: "Kumwe\\Access\\CapabilityDefinitionRegistry"
      source_path: src/Application/Authorization/CapabilityDefinitionRegistry.php
      target_path: src/CapabilityDefinitionRegistry.php
      kind: "class"
      public_methods:
        - "definition"
        - "isOwnedBy"
        - "ownedBy"
        - "register"
        - "removeOwner"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\CompositeResourceOwnershipReferences"
      new_fqcn: "Kumwe\\Access\\CompositeResourceOwnershipReferences"
      source_path: src/Application/Authorization/CompositeResourceOwnershipReferences.php
      target_path: src/CompositeResourceOwnershipReferences.php
      kind: "class"
      public_methods:
        - "__construct"
        - "sitesReferencing"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Identity\\Domain\\GrantScope"
      new_fqcn: "Kumwe\\Access\\GrantScope"
      source_path: src/Identity/Domain/GrantScope.php
      target_path: src/GrantScope.php
      kind: "class"
      public_methods:
        - "covers"
        - "equals"
        - "global"
        - "identifier"
        - "isGlobal"
        - "named"
        - "type"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\MembershipContextValidator"
      new_fqcn: "Kumwe\\Access\\MembershipContextValidator"
      source_path: src/Application/Authorization/MembershipContextValidator.php
      target_path: src/MembershipContextValidator.php
      kind: "interface"
      public_methods:
        - "current"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\OwnershipNarrowingRefused"
      new_fqcn: "Kumwe\\Access\\OwnershipNarrowingRefused"
      source_path: src/Application/Authorization/OwnershipNarrowingRefused.php
      target_path: src/OwnershipNarrowingRefused.php
      kind: "class"
      public_methods:
        - "__construct"
      public_properties:
        - "referencingSites"
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\OwnershipNarrowingUnbounded"
      new_fqcn: "Kumwe\\Access\\OwnershipNarrowingUnbounded"
      source_path: src/Application/Authorization/OwnershipNarrowingUnbounded.php
      target_path: src/OwnershipNarrowingUnbounded.php
      kind: "class"
      public_methods:
        - "__construct"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\OwnershipScope"
      new_fqcn: "Kumwe\\Access\\OwnershipScope"
      source_path: src/Application/Authorization/OwnershipScope.php
      target_path: src/OwnershipScope.php
      kind: "class"
      public_methods:
        - "contains"
        - "describe"
        - "equals"
        - "group"
        - "installation"
        - "isInstallation"
        - "requireSite"
        - "site"
        - "siteOrNull"
      public_properties:
        - "identifier"
        - "level"
        - "sites"
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\OwnershipScopeChangeRejected"
      new_fqcn: "Kumwe\\Access\\OwnershipScopeChangeRejected"
      source_path: src/Application/Authorization/OwnershipScopeChangeRejected.php
      target_path: src/OwnershipScopeChangeRejected.php
      kind: "class"
      public_methods:
        - "__construct"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\OwnershipScopeLevel"
      new_fqcn: "Kumwe\\Access\\OwnershipScopeLevel"
      source_path: src/Application/Authorization/OwnershipScopeLevel.php
      target_path: src/OwnershipScopeLevel.php
      kind: "enum"
      public_methods:
        - "reach"
        - "widerThan"
      public_properties: []
      public_constants:
        - "Group"
        - "Installation"
        - "Site"
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\OwnershipScopeNotPermitted"
      new_fqcn: "Kumwe\\Access\\OwnershipScopeNotPermitted"
      source_path: src/Application/Authorization/OwnershipScopeNotPermitted.php
      target_path: src/OwnershipScopeNotPermitted.php
      kind: "class"
      public_methods:
        - "__construct"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\OwnershipScopeNotSiteBound"
      new_fqcn: "Kumwe\\Access\\OwnershipScopeNotSiteBound"
      source_path: src/Application/Authorization/OwnershipScopeNotSiteBound.php
      target_path: src/OwnershipScopeNotSiteBound.php
      kind: "class"
      public_methods:
        - "__construct"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\OwnershipScopeRule"
      new_fqcn: "Kumwe\\Access\\OwnershipScopeRule"
      source_path: src/Application/Authorization/OwnershipScopeRule.php
      target_path: src/OwnershipScopeRule.php
      kind: "enum"
      public_methods:
        - "levels"
        - "permits"
      public_properties: []
      public_constants:
        - "SiteGroupOrInstallation"
        - "SiteOnly"
        - "SiteOrGroup"
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\ResourceOwnership"
      new_fqcn: "Kumwe\\Access\\ResourceOwnership"
      source_path: src/Application/Authorization/ResourceOwnership.php
      target_path: src/ResourceOwnership.php
      kind: "class"
      public_methods:
        - "of"
      public_properties:
        - "resource"
        - "scope"
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\ResourceOwnershipReferences"
      new_fqcn: "Kumwe\\Access\\ResourceOwnershipReferences"
      source_path: src/Application/Authorization/ResourceOwnershipReferences.php
      target_path: src/ResourceOwnershipReferences.php
      kind: "interface"
      public_methods:
        - "sitesReferencing"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\ResourceOwnershipScopePolicy"
      new_fqcn: "Kumwe\\Access\\ResourceOwnershipScopePolicy"
      source_path: src/Application/Authorization/ResourceOwnershipScopePolicy.php
      target_path: src/ResourceOwnershipScopePolicy.php
      kind: "class"
      public_methods:
        - "__construct"
        - "permits"
        - "register"
        - "rule"
        - "table"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\ResourcePolicyDefinition"
      new_fqcn: "Kumwe\\Access\\ResourcePolicyDefinition"
      source_path: src/Application/Authorization/ResourcePolicyDefinition.php
      target_path: src/ResourcePolicyDefinition.php
      kind: "class"
      public_methods:
        - "__construct"
        - "allowsSystemIdentity"
        - "enforceable"
        - "matches"
        - "overlaps"
        - "toArray"
      public_properties:
        - "capability"
        - "definitionVersion"
        - "id"
        - "installationGlobal"
        - "lifecycle"
        - "owner"
        - "systemIdentities"
        - "targets"
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\ResourcePolicyRegistry"
      new_fqcn: "Kumwe\\Access\\ResourcePolicyRegistry"
      source_path: src/Application/Authorization/ResourcePolicyRegistry.php
      target_path: src/ResourcePolicyRegistry.php
      kind: "class"
      public_methods:
        - "__construct"
        - "definitionFor"
        - "definitionsFor"
        - "ownedBy"
        - "register"
        - "removeOwner"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\ResourcePolicyTarget"
      new_fqcn: "Kumwe\\Access\\ResourcePolicyTarget"
      source_path: src/Application/Authorization/ResourcePolicyTarget.php
      target_path: src/ResourcePolicyTarget.php
      kind: "class"
      public_methods:
        - "__construct"
        - "matches"
        - "overlaps"
        - "toArray"
      public_properties:
        - "identifiers"
        - "type"
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\ResourceSiteOwnership"
      new_fqcn: "Kumwe\\Access\\ResourceSiteOwnership"
      source_path: src/Application/Authorization/ResourceSiteOwnership.php
      target_path: src/ResourceSiteOwnership.php
      kind: "interface"
      public_methods:
        - "scopeFor"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\ResourceSiteOwnershipConflict"
      new_fqcn: "Kumwe\\Access\\ResourceSiteOwnershipConflict"
      source_path: src/Application/Authorization/ResourceSiteOwnershipConflict.php
      target_path: src/ResourceSiteOwnershipConflict.php
      kind: "class"
      public_methods:
        - "__construct"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\ResourceSiteOwnershipWriter"
      new_fqcn: "Kumwe\\Access\\ResourceSiteOwnershipWriter"
      source_path: src/Application/Authorization/ResourceSiteOwnershipWriter.php
      target_path: src/ResourceSiteOwnershipWriter.php
      kind: "interface"
      public_methods:
        - "reassign"
        - "record"
        - "remove"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\SiteGroup"
      new_fqcn: "Kumwe\\Access\\SiteGroup"
      source_path: src/Application/Authorization/SiteGroup.php
      target_path: src/SiteGroup.php
      kind: "class"
      public_methods:
        - "__construct"
        - "contains"
      public_properties:
        - "identifier"
        - "members"
        - "name"
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\SiteGroupRegistry"
      new_fqcn: "Kumwe\\Access\\SiteGroupRegistry"
      source_path: src/Application/Authorization/SiteGroupRegistry.php
      target_path: src/SiteGroupRegistry.php
      kind: "interface"
      public_methods:
        - "all"
        - "group"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\SiteGroupUnknown"
      new_fqcn: "Kumwe\\Access\\SiteGroupUnknown"
      source_path: src/Application/Authorization/SiteGroupUnknown.php
      target_path: src/SiteGroupUnknown.php
      kind: "class"
      public_methods:
        - "__construct"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Application\\Authorization\\SiteGroupWriter"
      new_fqcn: "Kumwe\\Access\\SiteGroupWriter"
      source_path: src/Application/Authorization/SiteGroupWriter.php
      target_path: src/SiteGroupWriter.php
      kind: "interface"
      public_methods:
        - "addSite"
        - "removeSite"
        - "save"
      public_properties: []
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Proposed0.1.0 clean breaks AC-001 through AC-007; see docs/architecture.md."
    - old_fqcn: "Kumwe\\App\\Identity\\Domain\\AuthorizationDecision"
      new_fqcn: "Kumwe\\Access\\AuthorizationDecision"
      source_path: src/Identity/Domain/AuthorizationDecision.php
      target_path: src/AuthorizationDecision.php
      kind: "class"
      public_methods:
        - "__construct"
        - "toArray"
      public_properties:
        - "allowed"
        - "policy"
        - "reason"
        - "state"
      public_constants: []
      exceptions:
        - "InvalidArgumentException"
      serialization_contract: "Exact public member contracts and invariants in docs/public-api.md."
      compatibility: "Merge the second decision model into the canonical four-state result; AC-001."
  consumers:
    app_code:
      - "src/Administrator/Http/Handler/AdministratorCreateContentHandler.php"
      - "src/Administrator/Http/Handler/AdministratorExtensionActionHandler.php"
      - "src/Administrator/Http/Handler/AdministratorLoginHandler.php"
      - "src/Administrator/Http/Handler/AdministratorMediaHandler.php"
      - "src/Administrator/Http/Handler/AdministratorNavigationHandler.php"
      - "src/Administrator/Http/Handler/AdministratorRestoreContentHandler.php"
      - "src/Administrator/Http/Handler/AdministratorSettingsHandler.php"
      - "src/Administrator/Http/Handler/AdministratorTransitionContentHandler.php"
      - "src/Administrator/Http/Handler/AdministratorTrashContentHandler.php"
      - "src/Administrator/Http/Handler/AdministratorUpdateContentHandler.php"
      - "src/Administrator/Http/Handler/AdministratorWordingHandler.php"
      - "src/Administrator/Http/Middleware/AdministratorAuthorizationMiddleware.php"
      - "src/Administrator/Http/Middleware/AdministratorSessionMiddleware.php"
      - "src/Application/Authorization/AuthorizationDecision.php"
      - "src/Application/Authorization/AuthorizationDecisionRecorder.php"
      - "src/Application/Authorization/AuthorizationDenied.php"
      - "src/Application/Authorization/AuthorizationGateway.php"
      - "src/Application/Authorization/AuthorizationPolicyRegistry.php"
      - "src/Application/Authorization/AuthorizationResource.php"
      - "src/Application/Authorization/AuthorizationResourceOwnershipUnknown.php"
      - "src/Application/Authorization/CapabilityDefinition.php"
      - "src/Application/Authorization/CapabilityDefinitionRegistry.php"
      - "src/Application/Authorization/CompositeResourceOwnershipReferences.php"
      - "src/Application/Authorization/DenyByDefaultAuthorizationGateway.php"
      - "src/Application/Authorization/OwnershipNarrowingRefused.php"
      - "src/Application/Authorization/OwnershipNarrowingUnbounded.php"
      - "src/Application/Authorization/OwnershipScope.php"
      - "src/Application/Authorization/OwnershipScopeChangeRejected.php"
      - "src/Application/Authorization/OwnershipScopeNotPermitted.php"
      - "src/Application/Authorization/OwnershipScopeNotSiteBound.php"
      - "src/Application/Authorization/OwnershipScopeRule.php"
      - "src/Application/Authorization/ResourceOwnership.php"
      - "src/Application/Authorization/ResourceOwnershipReferences.php"
      - "src/Application/Authorization/ResourceOwnershipScopePolicy.php"
      - "src/Application/Authorization/ResourceOwnershipScopeService.php"
      - "src/Application/Authorization/ResourcePolicyDefinition.php"
      - "src/Application/Authorization/ResourcePolicyRegistry.php"
      - "src/Application/Authorization/ResourcePolicyTarget.php"
      - "src/Application/Authorization/ResourceSiteOwnership.php"
      - "src/Application/Authorization/ResourceSiteOwnershipConflict.php"
      - "src/Application/Authorization/ResourceSiteOwnershipWriter.php"
      - "src/Application/Authorization/SiteGroupAdministration.php"
      - "src/Application/Authorization/SiteGroupRegistry.php"
      - "src/Application/Authorization/SiteGroupWriter.php"
      - "src/Application/Authorization/StructuredLogAuthorizationDecisionRecorder.php"
      - "src/Application/Automation/AutomationManagementService.php"
      - "src/Application/Automation/Job/EnforceAuditRetentionHandler.php"
      - "src/Application/Automation/Job/PurgeAdministratorSessionsHandler.php"
      - "src/Application/Automation/Job/PurgeBusinessRecordIdempotencyHandler.php"
      - "src/Application/Automation/Job/PurgeIdempotencyRecordsHandler.php"
      - "src/Application/Automation/Job/PurgeStudioContentAuthoringContextsHandler.php"
      - "src/Application/Automation/Job/RebuildExtensionMapHandler.php"
      - "src/Application/Automation/Job/RecordAuditAnchorHandler.php"
      - "src/Application/Automation/Job/RotateRecordSecretsHandler.php"
      - "src/Application/Automation/Job/VerifyAuditTrailHandler.php"
      - "src/Application/Automation/Scheduler.php"
      - "src/Application/Automation/Worker.php"
      - "src/Application/Operations/MigrationLockRecoveryService.php"
      - "src/Application/Presentation/Dashboard/DashboardPreferenceService.php"
      - "src/Application/Presentation/Preference/PresentationPreferenceManager.php"
      - "src/Audit/Application/AuditAnchorWriter.php"
      - "src/Audit/Application/AuditRetentionService.php"
      - "src/Audit/Application/AuditTrailExporter.php"
      - "src/Audit/Application/AuditTrailVerifier.php"
      - "src/Audit/Infrastructure/Persistence/DoctrineAuditAnchorWriter.php"
      - "src/Audit/Infrastructure/Persistence/DoctrineAuditRetentionService.php"
      - "src/Audit/Infrastructure/Persistence/DoctrineAuditTrailExporter.php"
      - "src/Audit/Infrastructure/Persistence/DoctrineAuditTrailVerifier.php"
      - "src/BusinessDefinition/Application/BusinessDefinitionService.php"
      - "src/BusinessDefinition/Infrastructure/Persistence/DoctrinePackageDefinitionSynchronizer.php"
      - "src/BusinessIntegration/Application/IntegrationOperationsService.php"
      - "src/BusinessRecord/Application/BusinessRecordService.php"
      - "src/BusinessRecord/Application/PostingPeriodService.php"
      - "src/BusinessRecord/Application/RecordSecretRotation.php"
      - "src/BusinessRecord/Infrastructure/Persistence/DoctrineRecordSecretRotation.php"
      - "src/BusinessReporting/Application/ConsolidatedGroupReportScope.php"
      - "src/BusinessReporting/Application/ExportService.php"
      - "src/BusinessReporting/Application/ReportService.php"
      - "src/BusinessReporting/Domain/ExportArtifact.php"
      - "src/BusinessReporting/Domain/ReportDefinition.php"
      - "src/BusinessSchema/Application/BusinessSchemaExecutor.php"
      - "src/BusinessSchema/Application/BusinessSchemaPlanner.php"
      - "src/BusinessSchema/Application/BusinessSchemaService.php"
      - "src/BusinessSchema/Delivery/Administrator/ApproveBusinessSchemaPlanHandler.php"
      - "src/BusinessSchema/Delivery/Administrator/BusinessSchemaPlansHandler.php"
      - "src/BusinessSchema/Delivery/Administrator/CreateBusinessSchemaPlanHandler.php"
      - "src/BusinessSchema/Delivery/Administrator/CreateBusinessSchemaPurgePlanHandler.php"
      - "src/BusinessSchema/Delivery/Administrator/ExecuteBusinessSchemaPlanHandler.php"
      - "src/BusinessSchema/Delivery/Administrator/RecordBusinessSchemaRecoveryEvidenceHandler.php"
      - "src/BusinessSchema/Delivery/Administrator/RecoverBusinessSchemaPlanHandler.php"
      - "src/BusinessSchema/Delivery/Api/BusinessSchemaApiHandler.php"
      - "src/BusinessSecurity/Application/Administration/BusinessSecurityAdministrationService.php"
      - "src/BusinessSecurity/Application/Approval/ApprovalQueryService.php"
      - "src/BusinessSecurity/Application/Approval/ApprovalRequest.php"
      - "src/BusinessSecurity/Application/Approval/ApprovalRequestView.php"
      - "src/BusinessSecurity/Application/Approval/ApprovalRule.php"
      - "src/BusinessSecurity/Application/Approval/ApprovalService.php"
      - "src/BusinessSecurity/Application/MembershipDirectory.php"
      - "src/BusinessSecurity/Infrastructure/Persistence/DoctrineBusinessSecurityAdministrationRepository.php"
      - "src/BusinessSurface/Application/BusinessSurfaceCatalog.php"
      - "src/BusinessSurface/Application/Custom/CustomBusinessSurfaceDispatcher.php"
      - "src/Content/Application/ContentModelService.php"
      - "src/Content/Application/ContentService.php"
      - "src/Content/Infrastructure/Persistence/DoctrineContentModelRepository.php"
      - "src/Delivery/Console/Command/BusinessConsoleFailureMapper.php"
      - "src/Delivery/Console/Command/ConsoleAuthorizer.php"
      - "src/Delivery/Console/Command/CreateAccessTokenCommand.php"
      - "src/Delivery/Console/Command/ManageAccessCommand.php"
      - "src/Delivery/Http/Api/Business/BusinessApiResponder.php"
      - "src/Delivery/Http/Api/Business/BusinessRecordApiResponder.php"
      - "src/Delivery/Http/Api/Content/ContentApiResponder.php"
      - "src/Delivery/Http/Api/Extension/ExtensionApiHandler.php"
      - "src/Delivery/Http/Api/Extension/TrustStoreApiHandler.php"
      - "src/Delivery/Http/Api/Idempotency/HttpMutationPreauthorizer.php"
      - "src/Delivery/Http/Api/Idempotency/PersistentIdempotencyMiddleware.php"
      - "src/Delivery/Http/Api/Idempotency/SecretOnceIdempotencyMiddleware.php"
      - "src/Delivery/Http/Api/Identity/AccessControlApiHandler.php"
      - "src/Delivery/Http/Api/Navigation/NavigationApiResponder.php"
      - "src/Delivery/Http/Api/Site/SiteSettingsApiHandler.php"
      - "src/Extension/Application/Trust/TrustStore.php"
      - "src/Extension/Contribution/CanonicalManifestInterpreter.php"
      - "src/Extension/Contribution/CapabilityDefinition.php"
      - "src/Extension/Contribution/CapabilityDefinitionRegistry.php"
      - "src/Extension/Contribution/CoreExtensionContributions.php"
      - "src/Extension/Contribution/ExtensionContributionRegistrySet.php"
      - "src/Extension/Contribution/ResourcePolicyDefinition.php"
      - "src/Extension/Contribution/ResourcePolicyDefinitionRegistry.php"
      - "src/Extension/Infrastructure/DoctrineExtensionManager.php"
      - "src/Extension/Infrastructure/RedisLockedExtensionManager.php"
      - "src/Http/Middleware/BearerAuthenticationMiddleware.php"
      - "src/Http/Middleware/ProblemDetailsMiddleware.php"
      - "src/Identity/Application/Administration/AccessControlService.php"
      - "src/Identity/Application/Administration/AdministratorIdentityGateway.php"
      - "src/Identity/Application/Administration/AdministratorSessionStore.php"
      - "src/Identity/Application/Administration/TokenDelegationPreauthorizer.php"
      - "src/Identity/Application/Administration/TokenRotationPreauthorizer.php"
      - "src/Identity/Application/Authentication/AuthenticatedPrincipal.php"
      - "src/Identity/Application/Authentication/PrincipalGrant.php"
      - "src/Identity/Application/Authorization/AuthorizationPolicy.php"
      - "src/Identity/Application/Authorization/AuthorizationService.php"
      - "src/Identity/Application/Authorization/RoleGrantPolicy.php"
      - "src/Identity/Domain/CapabilityGrant.php"
      - "src/Identity/Infrastructure/Administration/DoctrineAdministratorIdentityGateway.php"
      - "src/Identity/Infrastructure/Administration/DoctrineAdministratorSessionStore.php"
      - "src/Infrastructure/Authorization/DoctrineGrantScopeOwnershipReferences.php"
      - "src/Infrastructure/Authorization/DoctrineResourceSiteOwnership.php"
      - "src/Infrastructure/Authorization/DoctrineResourceSiteOwnershipWriter.php"
      - "src/Infrastructure/Authorization/DoctrineSiteGroupRegistry.php"
      - "src/Infrastructure/Authorization/DoctrineSiteGroupWriter.php"
      - "src/Infrastructure/Automation/DoctrineJobQueue.php"
      - "src/Infrastructure/Automation/DoctrineQueueRuntimeOperations.php"
      - "src/Infrastructure/Automation/DoctrineScheduler.php"
      - "src/Infrastructure/Mcp/KumweMcpHandlers.php"
      - "src/Infrastructure/Mcp/McpToolErrorVocabulary.php"
      - "src/Infrastructure/Persistence/Migration/InterfaceMessageOverrideMigration.php"
      - "src/Infrastructure/Persistence/Migration/MigrationRunner.php"
      - "src/Infrastructure/Persistence/Migration/PeriodPostingLockMigration.php"
      - "src/Infrastructure/Persistence/Migration/ResourceOwnershipScopeMigration.php"
      - "src/Infrastructure/Persistence/Migration/StudioHostSessionMigration.php"
      - "src/InterfaceStandard/SurfaceDeclaration.php"
      - "src/Kernel/ContainerFactory.php"
      - "src/Localization/Application/MessageOverrideService.php"
      - "src/Media/Application/MediaService.php"
      - "src/Navigation/Application/NavigationService.php"
      - "src/Navigation/Application/PublicNavigation.php"
      - "src/Portal/Contribution/PortalNavigationRegistry.php"
      - "src/Portal/Contribution/PortalRouteRegistry.php"
      - "src/Portal/Http/Handler/PortalApprovalHandler.php"
      - "src/Portal/Http/Middleware/PortalAuthorizationMiddleware.php"
      - "src/Portal/Http/Middleware/PortalSessionMiddleware.php"
      - "src/Portal/Infrastructure/Session/DoctrinePortalSessionStore.php"
      - "src/Presentation/Application/ThemeMutationAuthorizer.php"
      - "src/Presentation/Infrastructure/DoctrineThemeMutationAuthorizer.php"
      - "src/Site/Application/SiteSettings.php"
      - "src/Site/Infrastructure/Persistence/CachedSiteSettings.php"
      - "src/Site/Infrastructure/Persistence/DoctrineSiteSettings.php"
      - "src/Studio/Application/Authoring/ContentStudioAuthoringContextAuthority.php"
      - "src/Studio/Application/Authoring/ContentStudioAuthoringTargetResolver.php"
      - "src/Studio/Application/Host/StudioHostSessionAuthority.php"
      - "src/Studio/Application/Media/StudioMediaHostPort.php"
      - "src/Studio/Application/Projection/StudioContentProjectionService.php"
      - "src/Workflow/Application/ContentTransitionAuthorizer.php"
      - "src/Workflow/Domain/WorkflowTransitionDefinition.php"
    configuration_and_di:
      - "src/Kernel/ContainerFactory.php"
    reflection_and_string_references: []
    fixtures_and_examples:
      - "tests/Functional/Extension/LiveSurfaceContractParityTest.php"
      - "tests/Integration/Authorization/BusinessGroupOwnershipEngineIntegrationTest.php"
      - "tests/Integration/Automation/AutomationManagementIntegrationTest.php"
      - "tests/Integration/Automation/WorkerConnectionLossKillPointIntegrationTest.php"
      - "tests/Integration/BusinessRecord/BusinessNumberSequenceIdentityIntegrationTest.php"
      - "tests/Integration/BusinessRecord/PostingPeriodLockIntegrationTest.php"
      - "tests/Integration/BusinessSurface/GeneratedBusinessQueryBudgetIntegrationTest.php"
      - "tests/Integration/Extension/AssetInspectionCustomViewIntegrationTest.php"
      - "tests/Integration/Extension/ExtensionOwnershipLifecycleIntegrationTest.php"
      - "tests/Integration/Extension/GeneratedExtensionLifecycleIntegrationTest.php"
      - "tests/Integration/Identity/AccessControlIntegrationTest.php"
      - "tests/Integration/Identity/AdministratorProvisioningIntegrationTest.php"
      - "tests/Integration/Localization/MessageOverrideIntegrationTest.php"
      - "tests/Support/AllowingAuditAuthorization.php"
      - "tests/Support/AuthorizationContext.php"
      - "tests/Support/CapabilityThemeAuthorizer.php"
      - "tests/Support/DashboardPreferenceTestRuntime.php"
      - "tests/Support/RestoreSecurityAcceptance.php"
      - "tests/Support/prepare-browser-contribution.php"
      - "tests/Unit/Administrator/Http/Handler/AdministratorAccessControlHandlerTest.php"
      - "tests/Unit/Administrator/Http/Handler/AdministratorContentEditorRetentionTest.php"
      - "tests/Unit/Administrator/Http/Handler/AdministratorLoginHandlerTest.php"
      - "tests/Unit/Administrator/Http/Middleware/AdministratorAuthorizationMiddlewareTest.php"
      - "tests/Unit/Application/Authorization/AdapterAuthorizationParityTest.php"
      - "tests/Unit/Application/Authorization/ApplicationAuthorizationTest.php"
      - "tests/Unit/Application/Authorization/AuthorizationPolicyRegistryTest.php"
      - "tests/Unit/Application/Authorization/BusinessGroupOwnershipTest.php"
      - "tests/Unit/Application/Authorization/DoctrineResourceSiteOwnershipTest.php"
      - "tests/Unit/Application/Authorization/DoctrineResourceSiteOwnershipWriterTest.php"
      - "tests/Unit/Application/Authorization/OwnershipScopeModelTest.php"
      - "tests/Unit/Application/Authorization/ResourceOwnershipScopeServiceTest.php"
      - "tests/Unit/Application/Authorization/ResourcePolicyDefinitionTest.php"
      - "tests/Unit/Application/Authorization/ResourcePolicyRegistryTest.php"
      - "tests/Unit/Application/Authorization/SiteGroupAdministrationTest.php"
      - "tests/Unit/Application/Authorization/SiteScopeContainmentIsNotAWideningTest.php"
      - "tests/Unit/Application/Authorization/StructuredLogAuthorizationDecisionRecorderTest.php"
      - "tests/Unit/Application/Automation/PurgeBusinessRecordIdempotencyHandlerTest.php"
      - "tests/Unit/Application/Automation/PurgeIdempotencyRecordsHandlerTest.php"
      - "tests/Unit/Application/Automation/PurgeStudioContentAuthoringContextsHandlerTest.php"
      - "tests/Unit/Application/Automation/WorkerTest.php"
      - "tests/Unit/Application/Presentation/Dashboard/DashboardPreferenceServiceTest.php"
      - "tests/Unit/BusinessIntegration/IntegrationOperationsServiceTest.php"
      - "tests/Unit/BusinessRecord/Application/PostingPeriodServiceTest.php"
      - "tests/Unit/BusinessReporting/ExportGenerationPolicyFenceTest.php"
      - "tests/Unit/BusinessReporting/ExportServiceTransactionTest.php"
      - "tests/Unit/BusinessReporting/LiveExportExecutionContextResolverTest.php"
      - "tests/Unit/BusinessReporting/RecordExportPipelineTest.php"
      - "tests/Unit/BusinessReporting/ReportApiDiscoveryTest.php"
      - "tests/Unit/BusinessReporting/ReportBrowserErrorResponseTest.php"
      - "tests/Unit/BusinessReporting/ReportPolicyInferenceTest.php"
      - "tests/Unit/BusinessSecurity/Application/ApprovalServiceTest.php"
      - "tests/Unit/BusinessSecurity/Application/BusinessSecurityAdministrationServiceTest.php"
      - "tests/Unit/BusinessSurface/Application/BusinessApprovalSurfaceServiceTest.php"
      - "tests/Unit/BusinessSurface/Application/BusinessMutationPlanServiceTest.php"
      - "tests/Unit/BusinessSurface/Application/BusinessSurfaceCatalogTest.php"
      - "tests/Unit/BusinessSurface/Application/BusinessSurfaceServiceTest.php"
      - "tests/Unit/BusinessSurface/Application/Custom/CustomBusinessActionExecutorTest.php"
      - "tests/Unit/BusinessSurface/Application/Custom/CustomBusinessHandlerRegistryTest.php"
      - "tests/Unit/Content/Application/ContentTranslationServiceTest.php"
      - "tests/Unit/Content/Application/ContributedContentTranslationTest.php"
      - "tests/Unit/Delivery/Console/Command/BusinessConsoleFailureMapperTest.php"
      - "tests/Unit/Delivery/Console/Command/DemoInstallCommandTest.php"
      - "tests/Unit/Delivery/Http/Api/Business/BusinessOperationStatusApiHandlerTest.php"
      - "tests/Unit/Delivery/Http/Api/Business/BusinessRecordApiHandlerTest.php"
      - "tests/Unit/Delivery/Http/Api/Extension/ExtensionApiHandlerTest.php"
      - "tests/Unit/Delivery/Http/Api/Idempotency/PersistentIdempotencyAuthorizationTest.php"
      - "tests/Unit/Demo/Infrastructure/VdmBusinessDemoInstallerTest.php"
      - "tests/Unit/Extension/Application/Trust/TrustStoreTest.php"
      - "tests/Unit/Extension/Contribution/ContributionDefinitionChecksumTest.php"
      - "tests/Unit/Extension/Contribution/ExtensionContributionRegistrySetTest.php"
      - "tests/Unit/Extension/Infrastructure/RedisLockedExtensionManagerTest.php"
      - "tests/Unit/Extension/Runtime/RestrictedExtensionContainerTest.php"
      - "tests/Unit/Identity/Application/Administration/AccessControlServiceTest.php"
      - "tests/Unit/Identity/Application/Administration/TokenDelegationPreauthorizerTest.php"
      - "tests/Unit/Identity/Application/Authentication/AuthenticatedPrincipalTest.php"
      - "tests/Unit/Identity/Application/Authorization/AuthorizationServiceTest.php"
      - "tests/Unit/Identity/Application/Authorization/RoleGrantPolicyTest.php"
      - "tests/Unit/Identity/Domain/AuthorizationDecisionTest.php"
      - "tests/Unit/Identity/Domain/CapabilityGrantTest.php"
      - "tests/Unit/Identity/Domain/GrantScopeTest.php"
      - "tests/Unit/Identity/Infrastructure/Administration/DoctrineAdministratorSessionStoreTest.php"
      - "tests/Unit/Identity/Infrastructure/Authentication/DoctrineAccessTokenVerifierTest.php"
      - "tests/Unit/InterfaceStandard/PresentationPreferenceManagerTest.php"
      - "tests/Unit/Localization/Application/MessageOverrideServiceTest.php"
      - "tests/Unit/Navigation/Application/NavigationServiceTest.php"
      - "tests/Unit/Navigation/Application/PublicNavigationTest.php"
      - "tests/Unit/Portal/Contribution/PortalContributionRegistryTest.php"
      - "tests/Unit/Portal/Http/PortalLoginHandlerTest.php"
      - "tests/Unit/Portal/Http/PortalSecurityBoundaryTest.php"
      - "tests/Unit/Portal/Http/PortalSecurityHandlerTest.php"
      - "tests/Unit/Portal/Infrastructure/Identity/DoctrinePortalPrincipalLoaderTest.php"
      - "tests/Unit/Portal/Presentation/PortalContributionRendererTest.php"
      - "tests/Unit/Portal/Presentation/PortalRendererAssetTest.php"
      - "tests/Unit/Site/Infrastructure/Persistence/DoctrineSiteSettingsTest.php"
      - "tests/Unit/Studio/Application/Authoring/ContentStudioAuthoringContextAuthorityTest.php"
      - "tests/Unit/Studio/Application/Authoring/ContentStudioAuthoringTargetResolverTest.php"
      - "tests/Unit/Studio/Application/Media/StudioMediaHostPortTest.php"
      - "tests/Unit/Workflow/Domain/WorkflowTest.php"
    external:
      - "SDK route/navigation capabilities, ManifestContributions invariants, public API/scaffold conformance."
  dependency_injection:
    mode: "config-provider"
    provider: "Kumwe\\Access\\ConfigProvider"
    factories:
      - "Kumwe\\Access\\Container\\AuthorizationPolicyRegistryFactory"
      - "Kumwe\\Access\\Container\\ResourceOwnershipScopePolicyFactory"
      - "Kumwe\\Access\\Container\\CompositeResourceOwnershipReferencesFactory"
    aliases: []
    service_lifetimes:
      - "Shared per trusted host container/runtime generation; explicit lifecycle rebuild."
    configuration_keys:
      - "kumwe.access.membership_resource_types"
      - "kumwe.access.reserved_ownership_rules"
      - "kumwe.access.reference_inspectors"
    provider_absence_reason: null
native_cpp: null
php_extension: null
tests:
  moved_or_added:
    - "tests/Case/ArchitectureTest.php"
    - "tests/Case/DecisionTest.php"
    - "tests/Case/FactoryTest.php"
    - "tests/Case/HostileDefinitionTest.php"
    - "tests/Case/IdentityTest.php"
    - "tests/Case/OwnershipTest.php"
    - "tests/Case/PortTest.php"
    - "tests/Case/RegistryTest.php"
  remain_in_app_or_consumer:
    - "App authority, trust/lifecycle, database/CAS, transactions/audit, delivery and direct invocation parity."
    - "SDK manifest graph, concrete surface definitions and lifecycle/contribution activation."
  split_tests:
    - "OwnershipScopeModelTest: portable scope/rule behavior here; exact App reserved table stays."
    - "AuthorizationPolicyRegistryTest: generic registry here; actual sensitive targets stay App."
  prohibited_duplicates:
    - "App old portable classes and unit behavior only removed with verified adoption."
    - "SDK historical Capability implementation retires in separately verified successor."
  corpora:
    - "resources/conformance/decisions-v1.json"
documentation:
  charter: "CHARTER.md"
  readme: "README.md"
  public_api: "docs/public-api.md"
  architecture: "docs/architecture.md"
  integration_or_consumer: "docs/integration.md"
  examples:
    - "examples/policies.php"
  changelog_record: "CHANGELOG.md ## 0.1.1"
release_expectations:
  version_policy: "Successor 0.1.1 uses published Context 0.1.1; independent verification precedes App adoption."
  expected_artifact_types:
    - "Composer package ZIP"
    - "GitHub source archive"
  required_checks:
    - "Access Context selected exact release must have passing independent external attestation."
    - "Protected main before publication; exact stable published release reports immutable true."
    - "composer check on PHP 8.5"
    - "Archive installed as dependency in isolated no-dev authoritative consumer"
    - "Independent release/source/artifact/manifest/Packagist verification"
  required_registry_or_installer: "Packagist + Composer"
  required_external_attestation: true
next_task:
  phase_name: "Correct verified dependency prerequisite; package release/verification; separate SDK successor; App adoption later"
  permitted_only_when:
    - "Access Context dependency release verification passes before publication of this package."
    - "Human-reviewed package immutable release receives independent passing external attestation."
    - "Verified SDK successor owns canonical Capability before App adoption."
  consumer_repository: "https://github.com/kumwe/extension-sdk"
  dependency_or_native_change: "Exact-pin verified Access Control and Contribution, retire SDK historical capability/owner model."
  namespace_or_api_replacements:
    - "Kumwe\\App\\Application\\Authorization\\AuthorizationDecision -> Kumwe\\Access\\AuthorizationDecision"
    - "Kumwe\\App\\Application\\Authorization\\AuthorizationDecisionRecorder -> Kumwe\\Access\\AuthorizationDecisionRecorder"
    - "Kumwe\\App\\Application\\Authorization\\AuthorizationDefinitionLifecycle -> Kumwe\\Access\\AuthorizationDefinitionLifecycle"
    - "Kumwe\\App\\Application\\Authorization\\AuthorizationDenied -> Kumwe\\Access\\AuthorizationDenied"
    - "Kumwe\\App\\Application\\Authorization\\AuthorizationGateway -> Kumwe\\Access\\AuthorizationGateway"
    - "Kumwe\\App\\Application\\Authorization\\AuthorizationPolicyRegistry -> Kumwe\\Access\\AuthorizationPolicyRegistry"
    - "Kumwe\\App\\Application\\Authorization\\AuthorizationResource -> Kumwe\\Access\\AuthorizationResource"
    - "Kumwe\\App\\Application\\Authorization\\AuthorizationResourceOwnershipUnknown -> Kumwe\\Access\\AuthorizationResourceOwnershipUnknown"
    - "Kumwe\\App\\Application\\Authorization\\CapabilityDefinition -> Kumwe\\Access\\CapabilityDefinition"
    - "Kumwe\\App\\Application\\Authorization\\CapabilityDefinitionRegistry -> Kumwe\\Access\\CapabilityDefinitionRegistry"
    - "Kumwe\\App\\Application\\Authorization\\CompositeResourceOwnershipReferences -> Kumwe\\Access\\CompositeResourceOwnershipReferences"
    - "Kumwe\\App\\Application\\Authorization\\MembershipContextValidator -> Kumwe\\Access\\MembershipContextValidator"
    - "Kumwe\\App\\Application\\Authorization\\OwnershipNarrowingRefused -> Kumwe\\Access\\OwnershipNarrowingRefused"
    - "Kumwe\\App\\Application\\Authorization\\OwnershipNarrowingUnbounded -> Kumwe\\Access\\OwnershipNarrowingUnbounded"
    - "Kumwe\\App\\Application\\Authorization\\OwnershipScope -> Kumwe\\Access\\OwnershipScope"
    - "Kumwe\\App\\Application\\Authorization\\OwnershipScopeChangeRejected -> Kumwe\\Access\\OwnershipScopeChangeRejected"
    - "Kumwe\\App\\Application\\Authorization\\OwnershipScopeLevel -> Kumwe\\Access\\OwnershipScopeLevel"
    - "Kumwe\\App\\Application\\Authorization\\OwnershipScopeNotPermitted -> Kumwe\\Access\\OwnershipScopeNotPermitted"
    - "Kumwe\\App\\Application\\Authorization\\OwnershipScopeNotSiteBound -> Kumwe\\Access\\OwnershipScopeNotSiteBound"
    - "Kumwe\\App\\Application\\Authorization\\OwnershipScopeRule -> Kumwe\\Access\\OwnershipScopeRule"
    - "Kumwe\\App\\Application\\Authorization\\ResourceOwnership -> Kumwe\\Access\\ResourceOwnership"
    - "Kumwe\\App\\Application\\Authorization\\ResourceOwnershipReferences -> Kumwe\\Access\\ResourceOwnershipReferences"
    - "Kumwe\\App\\Application\\Authorization\\ResourceOwnershipScopePolicy -> Kumwe\\Access\\ResourceOwnershipScopePolicy"
    - "Kumwe\\App\\Application\\Authorization\\ResourcePolicyDefinition -> Kumwe\\Access\\ResourcePolicyDefinition"
    - "Kumwe\\App\\Application\\Authorization\\ResourcePolicyRegistry -> Kumwe\\Access\\ResourcePolicyRegistry"
    - "Kumwe\\App\\Application\\Authorization\\ResourcePolicyTarget -> Kumwe\\Access\\ResourcePolicyTarget"
    - "Kumwe\\App\\Application\\Authorization\\ResourceSiteOwnership -> Kumwe\\Access\\ResourceSiteOwnership"
    - "Kumwe\\App\\Application\\Authorization\\ResourceSiteOwnershipConflict -> Kumwe\\Access\\ResourceSiteOwnershipConflict"
    - "Kumwe\\App\\Application\\Authorization\\ResourceSiteOwnershipWriter -> Kumwe\\Access\\ResourceSiteOwnershipWriter"
    - "Kumwe\\App\\Application\\Authorization\\SiteGroup -> Kumwe\\Access\\SiteGroup"
    - "Kumwe\\App\\Application\\Authorization\\SiteGroupRegistry -> Kumwe\\Access\\SiteGroupRegistry"
    - "Kumwe\\App\\Application\\Authorization\\SiteGroupUnknown -> Kumwe\\Access\\SiteGroupUnknown"
    - "Kumwe\\App\\Application\\Authorization\\SiteGroupWriter -> Kumwe\\Access\\SiteGroupWriter"
    - "Kumwe\\App\\Identity\\Domain\\GrantScope -> Kumwe\\Access\\GrantScope"
    - "Kumwe\\Extension\\Spi\\Identity\\Domain\\Capability -> Kumwe\\Access\\Capability"
    - "Kumwe\\App\\Identity\\Domain\\AuthorizationDecision -> Kumwe\\Access\\AuthorizationDecision"
  files_to_update:
    - "SDK src/Spi/Contribution/AdministratorNavigationDefinition.php"
    - "SDK src/Spi/Contribution/AdministratorRouteDefinition.php"
    - "SDK src/Spi/Portal/Contribution/PortalNavigationDefinition.php"
    - "SDK src/Spi/Portal/Contribution/PortalRouteDefinition.php"
    - "SDK src/Manifest/ManifestContributions.php"
    - "SDK composer.json and generated lock/public API conformance"
    - "App later: exact files in docs/consumer-inventory.json"
    - "App later: src/Kernel/ContainerFactory.php and host policy adapters"
  files_to_remove:
    - "SDK src/Spi/Identity/Domain/Capability.php after verified successor adoption"
    - "App src/Application/Authorization/AuthorizationDecision.php"
    - "App src/Application/Authorization/AuthorizationDecisionRecorder.php"
    - "App src/Application/Authorization/AuthorizationDefinitionLifecycle.php"
    - "App src/Application/Authorization/AuthorizationDenied.php"
    - "App src/Application/Authorization/AuthorizationGateway.php"
    - "App src/Application/Authorization/AuthorizationPolicyRegistry.php"
    - "App src/Application/Authorization/AuthorizationResource.php"
    - "App src/Application/Authorization/AuthorizationResourceOwnershipUnknown.php"
    - "App src/Application/Authorization/CapabilityDefinition.php"
    - "App src/Application/Authorization/CapabilityDefinitionRegistry.php"
    - "App src/Application/Authorization/CompositeResourceOwnershipReferences.php"
    - "App src/Application/Authorization/MembershipContextValidator.php"
    - "App src/Application/Authorization/OwnershipNarrowingRefused.php"
    - "App src/Application/Authorization/OwnershipNarrowingUnbounded.php"
    - "App src/Application/Authorization/OwnershipScope.php"
    - "App src/Application/Authorization/OwnershipScopeChangeRejected.php"
    - "App src/Application/Authorization/OwnershipScopeLevel.php"
    - "App src/Application/Authorization/OwnershipScopeNotPermitted.php"
    - "App src/Application/Authorization/OwnershipScopeNotSiteBound.php"
    - "App src/Application/Authorization/OwnershipScopeRule.php"
    - "App src/Application/Authorization/ResourceOwnership.php"
    - "App src/Application/Authorization/ResourceOwnershipReferences.php"
    - "App src/Application/Authorization/ResourceOwnershipScopePolicy.php"
    - "App src/Application/Authorization/ResourcePolicyDefinition.php"
    - "App src/Application/Authorization/ResourcePolicyRegistry.php"
    - "App src/Application/Authorization/ResourcePolicyTarget.php"
    - "App src/Application/Authorization/ResourceSiteOwnership.php"
    - "App src/Application/Authorization/ResourceSiteOwnershipConflict.php"
    - "App src/Application/Authorization/ResourceSiteOwnershipWriter.php"
    - "App src/Application/Authorization/SiteGroup.php"
    - "App src/Application/Authorization/SiteGroupRegistry.php"
    - "App src/Application/Authorization/SiteGroupUnknown.php"
    - "App src/Application/Authorization/SiteGroupWriter.php"
    - "App src/Identity/Domain/GrantScope.php"
    - "App src/Identity/Domain/AuthorizationDecision.php"
    - "App src/Identity/Domain/AuthorizationDecision.php"
  tests_to_remove:
    - "App tests/Unit/Identity/Domain/GrantScopeTest.php"
    - "App tests/Unit/Identity/Domain/AuthorizationDecisionTest.php"
    - "App tests/Unit/Application/Authorization/ResourcePolicyDefinitionTest.php"
    - "App tests/Unit/Application/Authorization/ResourcePolicyRegistryTest.php"
  tests_to_retain_or_add:
    - "App authority, trust/lifecycle, database/CAS, transactions/audit, delivery and direct invocation parity."
    - "SDK manifest graph, concrete surface definitions and lifecycle/contribution activation."
  di_or_provisioning_changes:
    - "Explicit host seven membership categories and44reserved rules; no default policy/gateway alias."
    - "Convert host SystemIdentity enum at boundary; preserve host principal/gateway/audit and trust activation."
  capability_index_changes:
    - "Regenerate after exact verified pins; remove old portable owners and duplicate class-unit tests together."
  changelog_and_evidence_changes:
    - "MIG009/CS009/NRM011 enabling extraction only; no roadmap completion."
  verification_commands:
    - "composer check"
    - "composer qa in App after later adoption"
    - "composer kumwe:capability-index-check"
    - "composer kumwe:core-growth-check"
concurrency:
  likely_conflict_files:
    - "SDK composer.json"
    - "SDK composer.lock"
    - "SDK public API and contribution consumers"
    - "App composer.json"
    - "App composer.lock"
    - "App src/Kernel/ContainerFactory.php"
    - "App migration ledgers and capability index"
  related_migrations:
    - "KUMWE-MIG-2026-004"
    - "KUMWE-MIG-2026-006"
  ownership_conflicts:
    - "Canonical SDK Capability transfer and Contribution adoption must be coordinated in SDK successor."
  integration_train: null
  resolution_rule: "semantic-preservation"
governance:
  roadmap_source_sha256: "a202155ef1a65f5ab293d4f8397ebf4ac430db7f1e877c776bbe7851e6fe18d8"
  roadmap_refs: []
  non_roadmap_refs:
    - "NRM-2026-011"
  completion_claim: false
decisions:
  - "AC-001 through AC-007: see docs/architecture.md."
  - "35 existing symbols plus 7 new explicit decision/composition types; no App production edits."
  - "User authorized continued draft development; governance release gate remains closed."
blockers:
  - "Selected Access Context 0.1.0 failed immutable-release verification. No passing external dependency attestation exists."
  - "Context 0.1.1 was published at cb6aefd575401192b83700d2d06c739e2a39285f; external adoption verification is separate."
  - "Package publication, independent verification, SDK successor and App adoption remain future tasks."
---

# Access Control draft handoff

## Migration/implementation summary

42 public types factor portable authorization from App and SDK. No host runtime is adopted; publication is
deliberately blocked pending independently verified Context dependency correction.

## Public API and responsibility

Every public type/member is documented in docs/public-api.md and reflected into resources/public-api/v1.json. Four
capability groups and explicit service construction are separately manifested. See CHARTER.md and
docs/architecture.md.

## Capability reuse/semantic input review

Exact source provenance appears above. Access Context owns the explicit context values. SDK Capability transfers here
only in a later verified successor train. The unverified Context development pin is never an attestation.

## Consumer inventory

The complete generated source/import/same-namespace search is frozen in docs/consumer-inventory.json. It includes
183production, 98test and 0configuration/tool paths. Review semantics rather than bulk-renaming host authority.

## Test ownership

Package-owned behavior/boundary/conformance evidence is enforced by tests/ownership.json and composer test:ownership.
The move/split/retain plan in docs/testing.md preserves actual App security, DB, delivery and lifecycle tests until
verified adoption removes duplicate portable units.

## Next-task execution notes

Follow docs/dependency-gate.md first; no release eligibility is claimed. Only after independent package release
verification may a separate SDK successor replace canonical Capability and compose contribution semantics, followed by
verified SDK release and a separate App adoption. See file-specific source and consumer maps.

## Drift check

Compare App and SDK current source with the full frozen baseline SHAs before any adoption. New portable behavior must
return upstream through a separately verified release. Do not add aliases, fallback paths, duplicate owners or
hand-edited lockfiles.

## Validation recipe and observed local results

PHP 8.5 package tests, strict static analysis, coding standards, manifest/schema/API checks, Composer audit and real
ZIP dependency consumer are the required final gates. The PR records their observed final-head outcomes externally. No
future commit/tag/archive identity or attestation is fabricated in this handoff.

## Membership and consumer-container completion (NRM-2026-023)

This successor checkpoint is tracked by PR #4. The original KUMWE-MIG-2026-009
source provenance above remains valid for its extracted classes. The additive
MembershipDirectory extraction was checked at App 24ecf956423c18933e824b43cea1bfb9127a79a9.
The exact symbol, file and consumer inventory is docs/membership-source-map.json.
Replace its old App FQCN with Kumwe\Access\MembershipDirectory in every listed
consumer only after a verified successor release. Delete the old interface then;
retain DoctrineMembershipDirectory and all DB/current-membership authority tests.
No implementation is copied to Approval.

MembershipDirectoryTest owns inherited signature/default conformance and absence
of an implicit authority binding. Real ServiceManager integration now resolves
every declared service from the no-dev archive consumer, verifies shared lifetime,
checks missing configuration refusals and rejects an implicit gateway. The host
consumer explicitly requires Laminas; it remains outside portable runtime dependencies.

A new immutable version record must be selected once upstream release evidence is
verified. The existing 0.1.0 manifest release coordinate is development metadata,
not a claim that the additional API is present in an existing released artifact.
Final release identity and all digest attestations remain external.
