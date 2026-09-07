# Security and compatibility

PHP 8.5 is the supported matrix. The package trusts no definitions as authority by itself. All input bounds are
byte/entry limits documented in architecture.md; identifiers are not automatically safe for SQL/HTML/shell use.
Factories validate configuration and require host policy. Invalid definitions fail atomically. No production I/O,
callbacks in definition values or ambient authentication exists.

Report security defects privately to repository maintainers through the repository security reporting channel when
available. Do not publish credentials or private tenant payloads. CI runs Composer audit, strict static analysis,
architecture/API and hostile-input tests. A passing package suite does not certify host authentication, trust or
persistence adapters.

SemVer applies to proposed public APIs after release. Pre-1.0 Kumwe dependencies are exact pins; the draft uses
exact published Context 0.1.1 in this successor. Clean-break decisions AC-001 through 007 are explicit initial-release
changes; no historical aliases, fallbacks or dual runtime owners are provided.
