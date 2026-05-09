# Graph Report - .  (2026-05-09)

## Corpus Check
- Corpus is ~20,470 words - fits in a single context window. You may not need a graph.

## Summary
- 126 nodes · 63 edges · 70 communities (62 shown, 8 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 2 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- [[_COMMUNITY_User Registration|User Registration]]
- [[_COMMUNITY_Settings Pages|Settings Pages]]
- [[_COMMUNITY_Fortify Provider|Fortify Provider]]
- [[_COMMUNITY_Auth Layouts|Auth Layouts]]
- [[_COMMUNITY_App Service Provider|App Service Provider]]
- [[_COMMUNITY_User Factory|User Factory]]
- [[_COMMUNITY_Profile Validation|Profile Validation]]
- [[_COMMUNITY_Two Factor Auth|Two Factor Auth]]
- [[_COMMUNITY_Logout Action|Logout Action]]
- [[_COMMUNITY_Test Case|Test Case]]
- [[_COMMUNITY_Base Controller|Base Controller]]
- [[_COMMUNITY_Components|Components]]

## God Nodes (most connected - your core abstractions)
1. `FortifyServiceProvider` - 6 edges
2. `User` - 5 edges
3. `partials.head` - 5 edges
4. `AppServiceProvider` - 4 edges
5. `UserFactory` - 4 edges
6. `profileRules()` - 3 edges
7. `partials.settings-heading` - 3 edges
8. `pages` - 3 edges
9. `CreateNewUser` - 2 edges
10. `nameRules()` - 2 edges

## Surprising Connections (you probably didn't know these)
- None detected - all connections are within the same source files.

## Communities (70 total, 8 thin omitted)

### Community 0 - "User Registration"
Cohesion: 0.2
Nodes (3): CreateNewUser, User, DatabaseSeeder

### Community 1 - "Settings Pages"
Cohesion: 0.29
Nodes (4): disable, $dispatch(, pages, partials.settings-heading

### Community 6 - "Profile Validation"
Cohesion: 0.83
Nodes (3): emailRules(), nameRules(), profileRules()

### Community 7 - "Two Factor Auth"
Cohesion: 0.5
Nodes (3): confirmTwoFactor, resetVerification, showVerificationIfNecessary

## Knowledge Gaps
- **7 isolated node(s):** `Controller`, `disable`, `$dispatch(`, `resetVerification`, `confirmTwoFactor` (+2 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **8 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Are the 2 inferred relationships involving `User` (e.g. with `.create()` and `.run()`) actually correct?**
  _`User` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Controller`, `disable`, `$dispatch(` to the rest of the system?**
  _7 weakly-connected nodes found - possible documentation gaps or missing edges._