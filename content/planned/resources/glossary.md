---
title: "AI Glossary"
slug: "glossary"
status: built-local
priority: P3
parent: /resources/
source_urls:
  - https://ai.csusystem.edu/resources/understanding-ai/
redirect_from: []
template: glossary
owner:
partners:
  - CSU Libraries
last_reviewed:
local_post_id: 233
production_post_id:
---

AI @ CSU / Resources

# AI Glossary

Plain-language definitions of common AI terms, and the ways you run into AI tools. This is not a technical guide or a list of approved tools. Examples show what a term looks like in practice and are not endorsements.

## Categories Overlap

One tool can be machine learning, generative, agent-like, cloud-hosted, and built into another application all at once. Treat the groups below as ways to describe a tool, not separate product types.

## Common AI Capabilities

Table: Capability | What it does | Uses and limits

| Capability | What it does | Uses and limits |
|---|---|---|
| Agent | Completes a specific task using a model, instructions, and sometimes outside tools. | Customer service chatbots and audio captioning tools. Reliability depends on its instructions, tools, and access to accurate information. |
| Agentic AI | Plans and runs a multi-step process toward a goal with limited supervision. | Booking workflows, digitizing documents with image editing and text recognition, account reconciliation. More autonomy needs tighter permissions, monitoring, and human review. |
| Generative AI | Creates new text, images, audio, video, or code from natural language prompts. | ChatGPT, Claude, Gemini, Copilot. Useful for drafting and summarizing. It can produce inaccurate, biased, or made-up content. |
| Machine learning (ML) | Learns patterns from data to predict, match, recommend, or correct. | Spell check, adaptive learning platforms, early-alert and advising tools. Results depend on the quality and coverage of the data. |

Note under table: Some examples have moved from ML to generative AI or combine several kinds of AI.

## Where and How You Encounter AI

Table: Form | What it means | What to remember

| Form | What it means | What to remember |
|---|---|---|
| API access | A programmatic connection to a model, often used to build it into another tool, such as a custom chatbot built on the Anthropic, Gemini, or OpenAI API. | Security, retention, and cost depend on the agreement and setup. |
| Browser extension or plugin | AI added to a web browser for specific features, such as Grammarly or Claude for Chrome. | It may read page content or what you type. Review its permissions. |
| Cloud-hosted service | A tool you reach through the vendor's site or app, where data is processed on the vendor's servers, such as Claude.ai, ChatGPT, or NotebookLM. | Data handling depends on your account, the contract, and your settings, not on the product name. |
| Embedded in everyday applications | AI-powered search, recommendations, and language features in apps like Netflix, Google Maps, or Amazon. | You may not see where AI is used, or what data it draws on. |
| Embedded in workplace applications | AI that adds tips, instructions, or automated actions inside tools such as Microsoft Office or Adobe Photoshop. | It still needs data and accessibility review. |
| Enterprise or licensed instance | Access to an AI tool under contract terms and data protections, with institutional login. Examples are chat.colostate.edu and the institutional Copilot license. | Stronger protections, but they do not automatically cover every feature, data type, or use. |
| Knowledge discovery tool | AI-powered search that finds, organizes, or summarizes sources, such as Perplexity, Scite, or Consensus. | Check the underlying sources and verify citations and claims. |
| Local model | A model that runs on your own device or server instead of in the cloud, such as Ollama, LM Studio, or llama.cpp. | More control, but not proof of security, accuracy, accessibility, or compliance. |
| Sandbox | A protected, contained space to test AI where data does not leave the environment. | Lowers exposure but is not risk-free. Confirm what information you may use. |

## Key Terms

Table: Term | What it means. Terms in alphabetical order. Data source: `data/glossary.yml`, group `key-terms`.

- Algorithm
- Bias
- Foundation model
- Hallucination
- Inference
- Large language model (LLM)
- Natural language processing (NLP)
- Prompt
- Prompt engineering
- Retrieval-augmented generation (RAG)
- Training
- Training data

Definitions are in `data/glossary.yml`. Render them from there. Do not retype them into this file.

## CSU Context

Table: Term | What it means here. Data source: `data/glossary.yml`, group `csu-context`.

- Data classification (link: Data Governance, https://ai.csusystem.edu/data-governance/)
- Enterprise data agreement
- FERPA
- Human oversight
- Responsible AI (link: Responsible AI Principles, https://ai.csusystem.edu/guidelines/ai-principles/)
- Tenant

## About These Definitions

Definitions are adapted from work by the Executive Vice President and Provost's AI working group at CSU Fort Collins and the CSU System's Understanding AI page, edited for plain language.


## Related

Buttons or links: Frequently Asked Questions (/resources/faq/), Using AI Responsibly (/resources/responsible-use/), AI Tools (/tools/), Teaching with AI (/teaching/), Research and AI (/research/), AI Governance (/about/governance/).

## Build notes

- Template `glossary`. Render tables from `data/glossary.yml` (one source of truth). Group order: capabilities, delivery, key-terms, csu-context.
- Add anchor IDs per term (slugified) so other pages can link to `/resources/glossary/#hallucination`.
- Native Elementor table or accordion. No HTML widget.
- Verify the `/tools/agents/` link in the Agent term before build.

## Editorial decisions needed

- Provost's working group owns the shared definitions. Confirm who at CSU Fort Collins reviews edits, and whether the page should say "adapted from" the System page.
- Provost text names "Pounce" (Georgia State), EAB Navigate, and Turnitin as ML examples. They are omitted here as vendor or other-institution references. Add back if wanted.
- Decide whether to link terms from other pages on first use.
