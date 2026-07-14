<?php

namespace Database\Seeders;

use App\Domain\Call\Models\CallLog;
use App\Domain\Contact\Models\Contact;
use App\Domain\Lead\Models\Lead;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class CallLogSeeder extends Seeder
{
    public function run(): void
    {
        $tenant   = Tenant::where('slug', 'demo')->firstOrFail();
        $agents   = User::where('tenant_id', $tenant->id)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['sales_agent', 'sales_manager']))
            ->get();
        $contacts = Contact::where('tenant_id', $tenant->id)->get();
        $leads    = Lead::where('tenant_id', $tenant->id)->get();

        $callData = [
            [
                'phone_number' => '+1-555-100-0001',
                'direction'    => 'outbound',
                'status'       => 'completed',
                'duration'     => 245,
                'contact_idx'  => 0,
                'transcript'   => "Agent: Hi Alice, this is John from Demo Corp. How are you today?\nAlice: I'm doing well, thanks. What can I do for you?\nAgent: I wanted to follow up on the software license proposal we sent last week.\nAlice: Yes, I reviewed it. We're very interested but need to discuss pricing with our CFO.\nAgent: Absolutely. Would Thursday work for a quick call with your team?\nAlice: Thursday at 2pm works great.\nAgent: Perfect, I'll send a calendar invite. Is there anything specific you'd like us to address?\nAlice: We want to understand the onboarding timeline and support options better.\nAgent: We'll cover that in detail. Looking forward to speaking with your team!",
                'ai_summary'   => 'Alice Johnson from TechCorp is interested in the software license proposal but needs CFO approval on pricing. A follow-up meeting was scheduled for Thursday at 2pm to cover onboarding timeline and support options.',
                'ai_insights'  => ['sentiment' => 'positive', 'keywords' => ['software license', 'pricing', 'CFO', 'onboarding', 'Thursday'], 'action_items' => ['Send calendar invite for Thursday 2pm', 'Prepare onboarding timeline document', 'Prepare support options overview'], 'outcome' => 'follow-up scheduled'],
                'days_ago'     => 2,
            ],
            [
                'phone_number' => '+1-555-100-0002',
                'direction'    => 'outbound',
                'status'       => 'completed',
                'duration'     => 180,
                'contact_idx'  => 1,
                'transcript'   => "Agent: Hello Bob, following up on the onboarding proposal.\nBob: Hi Lisa. Yes, I was actually about to call you. We have some concerns about the integration timeline.\nAgent: What specific concerns do you have?\nBob: We need everything live in 4 weeks. Your proposal said 6-8 weeks.\nAgent: I understand. Let me check with our technical team if we can fast-track for your case.\nBob: That would be great. Budget is approved, we just need the faster timeline.\nAgent: I'll confirm by end of day. Would a 4-week timeline make you ready to sign?\nBob: Absolutely.",
                'ai_summary'   => 'Bob Williams at Startup XYZ has budget approved but needs the onboarding reduced from 6-8 weeks to 4 weeks. Agent to confirm fast-track feasibility by end of day.',
                'ai_insights'  => ['sentiment' => 'positive', 'keywords' => ['onboarding', 'timeline', 'integration', '4 weeks', 'budget'], 'action_items' => ['Confirm fast-track timeline with technical team', 'Send updated proposal with 4-week timeline', 'Follow up by end of day'], 'outcome' => 'interested - pending timeline confirmation'],
                'days_ago'     => 1,
            ],
            [
                'phone_number' => '+1-555-100-0004',
                'direction'    => 'outbound',
                'status'       => 'completed',
                'duration'     => 420,
                'contact_idx'  => 3,
                'transcript'   => "Agent: Good morning David, this is John calling about the Enterprise IO annual plan.\nDavid: Yes, I was waiting for your call. We've reviewed everything internally.\nAgent: Excellent. What are your thoughts?\nDavid: We love the features but $60,000 is at the top of our budget. Can we discuss?\nAgent: Of course. What range were you thinking?\nDavid: Around $52,000 if we sign for two years.\nAgent: A two-year commitment at $52,000 — I think we can work with that. Let me get approval from my manager.\nDavid: Also, we need dedicated support. Is that included?\nAgent: At that tier, dedicated support is standard. I'll come back with a revised contract.\nDavid: Great. We're looking to close this month.",
                'ai_summary'   => 'David Brown at Enterprise IO wants to negotiate the $60K annual plan down to $52K for a two-year commitment. They also require dedicated support. Agent needs manager approval for pricing and will send a revised contract. Deal expected to close this month.',
                'ai_insights'  => ['sentiment' => 'positive', 'keywords' => ['pricing', 'negotiation', '2-year', 'dedicated support', 'annual plan'], 'action_items' => ['Get manager approval for $52K two-year pricing', 'Send revised contract with dedicated support clause', 'Close deal before month end'], 'outcome' => 'negotiation in progress'],
                'days_ago'     => 1,
            ],
            [
                'phone_number' => '+1-555-200-0003',
                'direction'    => 'outbound',
                'status'       => 'completed',
                'duration'     => 95,
                'lead_idx'     => 2,
                'transcript'   => "Agent: Hi Kevin, calling from Demo Corp. Do you have a moment?\nKevin: Sure, but I only have a few minutes.\nAgent: I'll be brief. We specialize in CRM solutions for tech companies. I saw Hot Lead IO recently raised Series A — congratulations!\nKevin: Thanks, yes it's been exciting.\nAgent: I'd love to schedule 20 minutes to show how we've helped similar companies scale their sales process.\nKevin: Send me an email with details. If it looks interesting I'll book time.\nAgent: Absolutely. What email should I use?\nKevin: kevin@hotlead.io\nAgent: Perfect, expect it within the hour.",
                'ai_summary'   => 'Kevin Walker at Hot Lead IO was briefly contacted. He is open to learning more but busy — prefers to review an email first before booking a demo. Email to be sent within the hour.',
                'ai_insights'  => ['sentiment' => 'neutral', 'keywords' => ['CRM', 'Series A', 'demo', 'email', 'scheduling'], 'action_items' => ['Send product overview email to kevin@hotlead.io', 'Follow up in 2 days if no response', 'Personalize pitch around Series A growth'], 'outcome' => 'email requested - follow-up pending'],
                'days_ago'     => 3,
            ],
            [
                'phone_number' => '+1-555-100-0006',
                'direction'    => 'outbound',
                'status'       => 'no_answer',
                'duration'     => null,
                'contact_idx'  => 5,
                'days_ago'     => 1,
            ],
            [
                'phone_number' => '+1-555-100-0008',
                'direction'    => 'inbound',
                'status'       => 'completed',
                'duration'     => 310,
                'contact_idx'  => 7,
                'transcript'   => "Henry: Hi, I'm calling because I saw your ad online. We're looking for a CRM for our 20-person sales team.\nAgent: Great to hear from you Henry! I'm Lisa. Can you tell me more about your current setup?\nHenry: We're using spreadsheets right now, which is a mess. We need something that handles leads, contacts, and has reporting.\nAgent: We check all those boxes. How soon are you looking to implement?\nHenry: We'd like to be up in 2 months.\nAgent: That's very doable. I'll set you up with a demo this week. Does Friday work?\nHenry: Friday morning is perfect.\nAgent: I'll book that now. Can I also send you our pricing guide in the meantime?\nHenry: Yes please.",
                'ai_summary'   => 'Henry Anderson called inbound after seeing an ad. He manages a 20-person sales team currently using spreadsheets and wants a full CRM in 2 months. A demo was scheduled for Friday morning. Pricing guide to be sent.',
                'ai_insights'  => ['sentiment' => 'positive', 'keywords' => ['inbound', 'spreadsheets', '20-person team', 'demo', 'pricing'], 'action_items' => ['Send pricing guide to Henry', 'Confirm Friday morning demo', 'Prepare demo focused on lead and contact management'], 'outcome' => 'demo scheduled'],
                'days_ago'     => 4,
            ],
            [
                'phone_number' => '+1-555-200-0001',
                'direction'    => 'outbound',
                'status'       => 'failed',
                'duration'     => null,
                'lead_idx'     => 0,
                'days_ago'     => 5,
            ],
            [
                'phone_number' => '+1-555-100-0003',
                'direction'    => 'outbound',
                'status'       => 'completed',
                'duration'     => 160,
                'contact_idx'  => 2,
                'transcript'   => "Agent: Hi Carol, this is John from Demo Corp. Is this a good time?\nCarol: Yes, quick question — I got your email but what exactly does your CRM do that others don't?\nAgent: Great question. Our key differentiator is the built-in AI call agent that auto-summarizes calls and generates insights.\nCarol: That sounds useful. We spend a lot of time writing call notes manually.\nAgent: Exactly what it solves. Would you like a quick 15-minute demo to see it in action?\nCarol: Yes, send me a time that works and I'll confirm.\nAgent: I'll send options this afternoon.",
                'ai_summary'   => 'Carol Davis is interested in the AI call summarization feature as her team wastes time writing manual notes. A 15-minute demo was agreed upon with times to be sent this afternoon.',
                'ai_insights'  => ['sentiment' => 'positive', 'keywords' => ['AI call agent', 'auto-summary', 'call notes', 'demo', 'differentiation'], 'action_items' => ['Send demo time options to Carol this afternoon', 'Highlight AI call summary in demo', 'Prepare ROI case around manual note reduction'], 'outcome' => 'demo agreed'],
                'days_ago'     => 2,
            ],
        ];

        foreach ($callData as $data) {
            $agent   = $agents->get(array_rand($agents->toArray()));
            $contact = isset($data['contact_idx']) ? $contacts->get($data['contact_idx']) : null;
            $lead    = isset($data['lead_idx'])    ? $leads->get($data['lead_idx'])    : null;

            $startedAt = now()->subDays($data['days_ago'])->setHour(rand(9, 17))->setMinute(rand(0, 59));
            $endedAt   = $data['duration'] ? (clone $startedAt)->addSeconds($data['duration']) : null;

            CallLog::firstOrCreate(
                [
                    'tenant_id'    => $tenant->id,
                    'phone_number' => $data['phone_number'],
                    'user_id'      => $agent->id,
                    'started_at'   => $startedAt,
                ],
                [
                    'tenant_id'    => $tenant->id,
                    'user_id'      => $agent->id,
                    'contact_id'   => $contact?->id,
                    'lead_id'      => $lead?->id,
                    'phone_number' => $data['phone_number'],
                    'direction'    => $data['direction'],
                    'status'       => $data['status'],
                    'duration'     => $data['duration'],
                    'transcript'   => $data['transcript'] ?? null,
                    'ai_summary'   => $data['ai_summary']  ?? null,
                    'ai_insights'  => $data['ai_insights'] ?? null,
                    'started_at'   => $startedAt,
                    'ended_at'     => $endedAt,
                ]
            );
        }

        $this->command->info(count($callData) . ' call logs seeded (with AI summaries and transcripts).');
    }
}
