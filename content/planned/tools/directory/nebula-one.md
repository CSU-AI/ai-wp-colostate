---
{
  "title": "Nebula One",
  "slug": "",
  "local_post_id": 65,
  "production_post_id": null,
  "wordpress_status": "publish",
  "menu_order": 7,
  "status": "approved",
  "approved_for_sensitive": true,
  "data_level_ceiling": "level-3-confidential",
  "cost": "Free for all CSU NetIDs",
  "tool_url": "https://chat.csusystem.edu/",
  "highlights": [
    "CSU's own AI platform",
    "Build agents with your own files",
    "No per-user license"
  ],
  "taxonomies": {
    "aicsu_role": [
      "faculty",
      "researcher",
      "staff"
    ],
    "aicsu_data": [
      "level-1-public",
      "level-2-internal",
      "level-3-confidential"
    ],
    "aicsu_task": [
      "analyze-documents",
      "build-agent",
      "research",
      "summarize"
    ],
    "aicsu_complexity": [
      "advanced",
      "beginner",
      "intermediate"
    ]
  },
  "data_examples": [
    "my-own-files",
    "public-information",
    "sensitive-business"
  ],
  "demo": false
}
---

CSU's AI platform at chat.csusystem.edu, built by Cloudforce and running inside CSU's Azure tenant. Every model it uses is hosted in CSU's own Azure AI Foundry, and conversations are stored in CSU's tenant and are not used to train outside models. Nebula One holds many agents. CSU-GPT is the general-purpose agent most people start with, and anyone with a NetID can build a personal agent that uses a website, uploaded files, or a basic API as its knowledge source. Official agents, set up with the AI strategy team, can connect to MCP servers and indexed document libraries and can call other agents. Web Search, when turned on, sends queries to Brave's search service outside CSU's tenant, so turn it off before working with Level 3 confidential data. There is no per-user license, and usage is covered centrally, which makes it the best choice for agents that need to reach a broad or public audience.
