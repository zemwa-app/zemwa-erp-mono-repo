# Zemwa ERP - Versioning Requirements

This document outlines the versioning strategy, requirements, and baseline matrices for the core ERP system and its individual product bundles.

## Consolidated Release Version: v2.0.0
The target release for this consolidated package update is **v2.0.0**. This release represents the integration of the Pro Bundle and the route cleaning updates.

---

## Baseline Version Specifications

To manage independent module lifecycles, the system uses specific baseline versions for each layer:

| Component | Base Version | Notes |
| :--- | :--- | :--- |
| **Core System** | `v6.0.15` | The baseline web core framework version. |
| **Universal Bundle** | `v2.0.15` | Baseline version for universal features. |
| **Pro Bundle** | *No Base Version* | Brand new release developed recently. Starting version is **v2.0.0**. |

---

## Requirements & Guidelines

1. **Independent Sub-module Versioning**:
   * Any changes made to `Universal Bundle` or `Pro Bundle` must be tracked independently.
   * Version updates for sub-modules must be recorded separately within the consolidated developer release logs.

2. **Semantic Versioning (SemVer)**:
   * **Major (X.y.z)**: Breaking changes, architectural overhauls.
   * **Minor (x.Y.z)**: New features, new sub-module additions, database schema expansions.
   * **Patch (x.y.Z)**: Bug fixes, UI/translation refinements, routing adjustments.

3. **Changelog Separation**:
   * Release notes must isolate Core System changes from bundle changes. This prevents updates in the Pro Bundle from confusing users running the basic Universal version.
