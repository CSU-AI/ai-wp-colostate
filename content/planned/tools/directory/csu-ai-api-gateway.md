---
{
  "title": "CSU AI API Gateway",
  "slug": "",
  "local_post_id": 66,
  "production_post_id": null,
  "wordpress_status": "publish",
  "menu_order": 15,
  "status": "approved",
  "approved_for_sensitive": false,
  "data_level_ceiling": "level-2-internal",
  "cost": "Usage based, billed to your Kuali account with a monthly cap you set",
  "tool_url": "",
  "highlights": [
    "One key, many models",
    "Hard monthly spending cap",
    "Billed through Kuali"
  ],
  "taxonomies": {
    "aicsu_role": [
      "faculty",
      "researcher",
      "staff"
    ],
    "aicsu_data": [
      "level-1-public",
      "level-2-internal"
    ],
    "aicsu_task": [
      "automation",
      "build-agent",
      "code",
      "research"
    ],
    "aicsu_complexity": [
      "advanced",
      "developer"
    ]
  },
  "data_examples": [
    "confidential-research",
    "my-own-files",
    "public-information"
  ],
  "demo": false
}
---

The self-service way to get an AI API key at CSU. Request access through the portal, get approved, and receive a single key that reaches OpenAI models and Anthropic Claude models hosted in CSU's Azure tenant. It does the same job as setting up your own Azure AI Foundry project, without the setup work: no separate Azure subscription, no per-team key management, no personal card. Calls fall under CSU's enterprise data agreement with Microsoft, so your content is not used to train models. Every key carries a hard monthly dollar cap that stops cleanly when reached, and usage is metered per key and exported monthly to Kuali for chargeback to your account. Use it from your own code, or point a tool such as Claude Code at it. Google models are not available, because Google restricts them to its own platforms.

To request a key, submit the form with your Kuali account number, your financial administrator or accountant contact, and the monthly cap you want. Requests are approved manually, and the AI strategy team is not notified automatically, so send a message after you submit. Keys are shown once at creation and cannot be retrieved later.
