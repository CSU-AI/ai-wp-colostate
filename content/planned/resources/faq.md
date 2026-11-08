---
title: "AI at CSU FAQ"
slug: "faq"
status: built-local
priority: P2
parent: /resources/
source_urls:
  - https://ai.colostate.edu/ai-tools/
  - https://ai.csusystem.edu/resources/faq/
redirect_from: []
template: faq
owner:
partners: []
last_reviewed:
local_post_id: 235
production_post_id:
---

AI @ CSU / Resources

# AI at CSU FAQ

Common questions about AI at CSU Fort Collins. For System-wide guidance, see the [CSU System AI FAQ](https://ai.csusystem.edu/resources/faq/).

## Getting Started

### What Is AI @ CSU?

AI @ CSU is the Fort Collins home for approved AI tools, training, and guidance for teaching and research. The Division of IT runs it. For System-level policy and data governance, we link to the CSU System sites.

### Where Do I Start if I Just Want to Use AI for My Work?

Sign in to [Microsoft Copilot Chat](https://m365.cloud.microsoft/chat) with your CSU NetID and use it for drafting, summarizing, and questions. Keep your data at or below what the tool is approved for. See [Approved AI Tools](/tools/).

### How Is AI Governed Here?

Fort Collins follows the CSU System's AI governance guidelines and data classification rules. Read the [AI Governance Guidelines](https://datagov.csusystem.edu/ai-governance-guidelines/) from CSU System Data Governance.

## Tools

### Which Tools Are Approved for CSU Data?

These enterprise tools are provided through CSU's Microsoft 365 agreement or hosted by CSU:

- Microsoft Copilot Chat, when you sign in with your CSU NetID
- Microsoft 365 Copilot Premium (also called M365 Copilot Premium), available to faculty and staff, and approved for Level 3 confidential data
- Microsoft Teams Premium, for meeting notes and recaps
- CSU-GPT, a secure CSU-hosted chat tool for students, faculty, and staff

What each tool may handle depends on the data level and its settings. See [Approved AI Tools](/tools/) and [Use AI With Level 3 Confidential Data](/tools/level-3-data/). RamGPT, a chatbot, is not live yet.

### Can I Use ChatGPT, Claude, or Gemini for CSU Work?

Yes, for public information (Level 1). Internal (Level 2) information needs approval from your unit's data steward. Many commercial tools can collect and store what you enter, and may use it to train future models. Turning off training in your settings does not guarantee privacy. Do not paste in research data, student records, or confidential work.

### Which Tool Should I Choose?

Use [Help Me Choose](/tools/help-me-choose/) to match a task and data level to a tool, or see the [tool comparison](/tools/comparison/).

### Can I Use Other Tools to Transcribe Meetings?

Check the Division of IT knowledge base article on the [use of third-party AI and transcription tools](https://csusystem.freshservice.com/support/solutions/articles/23000168836-Usage-of-Third-Party-AI-Transcription-tools) first. Teams Premium keeps recaps inside CSU's Microsoft tenant.

## Data and Privacy

### Can I Upload Student Data to an AI Tool?

Only to an enterprise tool approved for that kind of data, and only when FERPA allows the use. Never to a consumer tool. If you are unsure, ask before you upload. See [CSU System Data Governance](https://datagov.csusystem.edu/).

### How Do I Know What Level My Data Is?

CSU sorts data into four levels from Public to Restricted. Read the [data classification levels](https://datagov.csusystem.edu/data-classification-levels/). For Level 3 confidential data, see [Use AI With Level 3 Confidential Data](/tools/level-3-data/).

### What Is the Green Shield in Copilot?

The green shield is what people at CSU call Microsoft's enterprise data protection shield. It means you are signed in with your CSU account, so your prompts and responses are protected under CSU's agreement with Microsoft. If you do not see it, you are not protected.

### Do I Need to Turn Off Web Search?

For Level 3 data, yes. When web search is on, a search query written from your prompt goes to an outside search service, and that query can include pieces of what you entered. Copilot sends these queries to Bing, and CSU-GPT and Nebula One send them to Brave. If you need current information from the web, ask for it in a separate conversation with no confidential data.

## Teaching and Research

### Can I Use AI in My Courses?

Instructors decide what AI use is allowed in their courses. Follow your department and program guidance. See [Teaching and Learning with AI](/teaching/), including [syllabus language](/teaching/syllabus-language/) and [assignment design](/teaching/assignment-design/).

### Can I Use AI With Research Data or in Grant Work?

It depends on the data and the funder. Check data classification, IRB requirements, and sponsor rules before you use AI with research data. See [AI Research Resources](/research/).

## Getting Help

### Who Do I Contact?

Start with [AI Contact and Support](/resources/contact/) for tool access, teaching, research, and general questions. The Division of IT service desk can help with sign-in and access.

### How Do I Report a Problem or a Data Exposure?

Report a data incident through the [Data Governance contact page](https://datagov.csusystem.edu/contact-us/). Do it as soon as you notice it.

### Can I Contribute a Post or Share a Project?

Yes. We welcome stories and projects about AI at CSU or in higher education from across the community. Send your idea through [AI Contact and Support](/resources/contact/).

## Related

- [AI Glossary](/resources/glossary/)
- [Using AI Responsibly](/resources/responsible-use/)
- [How We Use AI on This Site](/about/ai-use-on-this-site/)
- [CSU System AI FAQ](https://ai.csusystem.edu/resources/faq/)

## Build notes

- Template `faq`. Page-scoped CSS only if needed. Built with one Accordion widget per section (decided November 2026), jump links, and section bands. See `wordpress/build-notes/page-building.md`.
- Use real lists. One H1. Add anchor IDs to each question.
- Local links need the `multisite.local/ai/` prefix when built.

## Editorial decisions needed

- Confirm "Fort Collins follows the System guidelines" as the governance answer until a Fort Collins governance page exists. Update when one does.
