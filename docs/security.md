# Security and compatibility

PHP 8.5 is the supported matrix. The package trusts no definitions as authority by itself. All input bounds are
byte/entry limits documented in architecture.md; identifiers are not automatically safe for SQL/HTML/shell use.
Factories validate configuration and require host policy. Invalid definitions fail atomically. No production I/O,
callbacks in definition values or ambient authentication exists.

Report security defects privately to repository maintainers through the repository security reporting channel when
available. Do not publish credentials or private tenant payloads. CI runs Composer audit, strict static analysis,
architecture/API and hostile-input tests. A passing package suite does not certify host authentication, trust or
persistence adapters.

SemVer applies to the public API. Pre-1.0 Kumwe dependencies are exact pins; the package requires Access Context
0.1.2. Compatibility decisions AC-001 through AC-008 are documented in [architecture](architecture.md).
Host authority and portable mechanisms have separate canonical owners.
