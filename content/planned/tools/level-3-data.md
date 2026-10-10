---
title: "Use AI With Level 3 Confidential Data"
slug: "level-3-data"
status: drafting
priority: P0
parent: /tools/
source_urls:
  - https://datagov.csusystem.edu/data-classification-levels/
  - https://datagov.csusystem.edu/ai-governance-guidelines/
  - https://learn.microsoft.com/en-us/microsoft-365/copilot/manage-public-web-access
  - https://learn.microsoft.com/en-us/microsoft-365/copilot/enterprise-data-protection
redirect_from: []
template: guidance
owner:
partners:
  - Data Governance
  - Division of IT
last_reviewed: 2026-09-23
local_post_id:
production_post_id:
---

# Use AI With Level 3 Confidential Data

Some CSU-approved AI tools can handle Level 3 confidential data, but only when you use them in the right way. This page lists those tools, the settings each one needs, and the steps to take before you start.

## What counts as Level 3

Level 3 data is intended for limited use within the CSU System and carries considerable risk if it is exposed. Examples include personnel records, donor information, assessment data, and personally identifiable information that is not Level 4. See the [CSU data classification levels](https://datagov.csusystem.edu/data-classification-levels/) for the full definitions.

Never enter these in any AI tool, at any level:

- Passwords, API keys, or other credentials, even though the classification places passwords at Level 3
- Level 4 restricted data, such as Social Security numbers, bank or card numbers, government IDs, biometric data, and controlled unclassified information

## Before you start

- **Tool approval does not grant data access.** You still need permission to use the data itself. Follow the requirements set by the data steward for that data.
- **Use your CSU account.** Level 3 approval applies only when you sign in with your CSU credentials. A personal account on the same product is not approved.
- **Turn off web search.** See the next section.
- **Keep only what you need.** Chat history, meeting transcripts, and AI output are university records. They can be retained, requested, and reviewed like other records, so do not create more of them than the work requires.

## Why web search must be off

When web search is on, the tool writes a short search query based on your prompt and sends it to an outside search service. That query can contain names, case details, or other fragments of the confidential information you entered, and it leaves the protections that cover the rest of your conversation.

- **Microsoft Copilot Chat and Copilot Studio** send web queries to Bing. Microsoft's data protection agreement with CSU does not cover those queries; Bing handles them as an independent data controller under its own terms.
- **Nebula One and CSU-GPT** send web queries to Brave's search service when Web Search is turned on.

Turn web search off before you enter Level 3 data. If you need current information from the web, ask for it in a separate conversation that contains no confidential data.

## Approved tools and required settings

| Tool | Where to sign in | Required for Level 3 |
|---|---|---|
| Microsoft Copilot Chat | [m365.cloud.microsoft/chat](https://m365.cloud.microsoft/chat/) | Enterprise data protection shield visible; web search off |
| Microsoft Teams Premium | Teams, with a Teams Premium license | Meeting organizer decides whether recaps and transcripts are appropriate for the meeting |
| CSU-GPT | [chat.csusystem.edu/chat/onechat](https://chat.csusystem.edu/chat/onechat) | Web Search off |
| Nebula One agents | [chat.csusystem.edu](https://chat.csusystem.edu/) | Web Search off; see the agent rules below |
| Copilot Studio | [copilotstudio.microsoft.com](https://copilotstudio.microsoft.com/) | See the agent rules below |
| Microsoft Foundry | [ai.azure.com](https://ai.azure.com/) | Data Governance Steering Committee approval; abuse-monitoring retention off; Microsoft-hosted models only |
| CSU AI API Gateway | Request form | Security review of the application that sends the data |

## Building agents that use Level 3 data

Agents raise a different risk than chat. An agent can answer many people, connect to other systems, and act with the permissions of the person who built it.

### Copilot Studio

- **Never publish a Level 3 agent without authentication.** An agent set to "no authentication" can be used by anyone who has the link.
- **Use each person's own credentials.** If an agent's knowledge sources or connectors run under your credentials, everyone who uses the agent can reach whatever you can reach. Set connections to use the signed-in user's credentials so people only see data they are already allowed to see.
- **Keep knowledge sources inside CSU's Microsoft 365 tenant.** Public website knowledge sources and web search send queries to Bing, as described above. Turn them off for Level 3 agents.
- **Get a security review before connecting to anything outside Microsoft 365.** Custom connectors, HTTP requests, and MCP connections to outside services count as external tool connections, and the [AI governance guidelines](https://datagov.csusystem.edu/ai-governance-guidelines/) require a formal security review for them.
- **Share only with people cleared for the data.** Sharing an agent shares what it can reveal.

### Nebula One

- Personal agents that use a website or an outside API as a knowledge source are external connections. Do not combine them with Level 3 data unless the connection has had a security review.
- Official agents that connect to MCP servers or campus systems are set up with the AI strategy team, which includes the security review for those connections.

## Building software on the API Gateway or Foundry

The CSU AI API Gateway and the models in Microsoft Foundry are approved for Level 3 data. That approval covers the call to the model. It does not cover the application you build around it.

Your application is its own system. It decides who can sign in, what it stores, what it logs, how long it keeps data, and where its output goes. A model call made under CSU's agreement with Microsoft can still end up in an unencrypted log file, a public web page, or a shared spreadsheet because of how the application was built.

Before an application sends Level 3 data to the gateway or to Foundry:

- Get a security review of the application, including authentication, storage, logging, and retention.
- Store API keys in a secrets manager or environment variable, never in source code or shared files.
- For Foundry projects, get Data Governance Steering Committee approval and written confirmation that abuse-monitoring retention is turned off for your subscription.
- Use only models hosted by Microsoft in CSU's tenant. Third-party models listed in Foundry may be governed by their provider's own terms.

If you share a tool or service with other people, you are building software, even if it is a small script.

## Editorial decisions needed

- Record the Data Governance Steering Committee approval date for the Level 3 tool list before this page moves past `content-review`.
- Confirm the exact name and location of the web search control in Copilot Chat, CSU-GPT, and Nebula One so the page can give click-by-click steps.
- Confirm the security review intake for agents and applications, and link it from the Copilot Studio, Nebula One, and API Gateway sections.
- Confirm whether a Copilot Studio environment or data loss prevention policy is required for Level 3 agents.
- Decide whether personal developer tools, such as Claude Code pointed at the gateway, need their own review for Level 3 work. Some of these tools have their own web search or telemetry features.
- Confirm Teams Premium recap and transcript retention settings for meetings that discuss Level 3 information.
