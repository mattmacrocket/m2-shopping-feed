# Licensing notice

The imported Rocket Web sources contain conflicting package-level license metadata:

- Every imported `composer.json` declares `GPL-3.0`.
- Imported PHP file headers state that the source is subject to OSL-3.0.
- The Rocket Web READMEs that discuss licensing link to an OSL license file.
- None of the four imported revisions includes the referenced license file.

This consolidation uses OSL-3.0. It retains every per-file OSL-3.0 notice, declares `OSL-3.0` in Composer, and includes the OSL-3.0 text in [LICENSE.txt](LICENSE.txt). This follows the explicit notice attached to the source files themselves.

The imported Composer metadata discrepancy is retained here as provenance. The package metadata, source notices, and bundled license in this repository are aligned on OSL-3.0.
