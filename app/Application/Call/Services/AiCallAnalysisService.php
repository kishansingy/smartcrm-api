<?php

namespace App\Application\Call\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiCallAnalysisService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.openai.api_key', '');
        $this->model   = config('services.openai.model', 'gpt-4o-mini');
        $this->baseUrl = 'https://api.openai.com/v1';
    }

    /**
     * Generate a structured summary and insights from a call transcript.
     *
     * @return array{summary: string, insights: array}
     */
    public function analyzeTranscript(string $transcript, array $context = []): array
    {
        $contextStr = '';
        if (!empty($context['contact_name'])) {
            $contextStr .= "Customer: {$context['contact_name']}. ";
        }
        if (!empty($context['agent_name'])) {
            $contextStr .= "Agent: {$context['agent_name']}. ";
        }

        $prompt = <<<PROMPT
You are a CRM call analysis assistant. Analyze the following call transcript and provide:
1. A concise call summary (2-4 sentences)
2. Customer sentiment (positive/neutral/negative)
3. Key topics discussed (up to 5 keywords)
4. Action items for the agent (up to 3 bullet points)
5. Call outcome (e.g., interested, not interested, follow-up scheduled, issue resolved)

{$contextStr}

Transcript:
{$transcript}

Respond ONLY with valid JSON in this exact structure:
{
  "summary": "...",
  "sentiment": "positive|neutral|negative",
  "keywords": ["...", "..."],
  "action_items": ["...", "..."],
  "outcome": "..."
}
PROMPT;

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'       => $this->model,
                    'messages'    => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.3,
                    'max_tokens'  => 500,
                ]);

            if ($response->failed()) {
                Log::warning('OpenAI call analysis failed', ['status' => $response->status()]);
                return $this->fallbackAnalysis($transcript);
            }

            $content = $response->json('choices.0.message.content', '');
            $parsed  = json_decode($content, true);

            if (!$parsed || !isset($parsed['summary'])) {
                return $this->fallbackAnalysis($transcript);
            }

            return [
                'summary'  => $parsed['summary'],
                'insights' => [
                    'sentiment'    => $parsed['sentiment']    ?? 'neutral',
                    'keywords'     => $parsed['keywords']     ?? [],
                    'action_items' => $parsed['action_items'] ?? [],
                    'outcome'      => $parsed['outcome']      ?? '',
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('AI call analysis exception', ['error' => $e->getMessage()]);
            return $this->fallbackAnalysis($transcript);
        }
    }

    /**
     * Generate a human-readable report summary from multiple call logs.
     */
    public function generateReportNarrative(array $reportData): string
    {
        $total     = $reportData['summary']['total']        ?? 0;
        $completed = $reportData['summary']['completed']    ?? 0;
        $avgDur    = $reportData['summary']['avg_duration'] ?? 0;
        $withAI    = $reportData['summary']['with_summary'] ?? 0;

        $prompt = <<<PROMPT
You are a CRM analyst. Given the following call statistics, write a concise executive summary (3-5 sentences) highlighting performance, trends, and recommendations.

Total calls: {$total}
Completed calls: {$completed}
Average call duration: {$avgDur} seconds
Calls with AI summary: {$withAI}
By status: {$this->encodeForPrompt($reportData['by_status'] ?? [])}
By agent: {$this->encodeForPrompt($reportData['by_agent'] ?? [])}

Write a professional, concise narrative summary only. No JSON, no bullet points.
PROMPT;

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'       => $this->model,
                    'messages'    => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.5,
                    'max_tokens'  => 300,
                ]);

            if ($response->failed()) {
                return "Report generated with {$total} total calls and {$completed} completed.";
            }

            return trim($response->json('choices.0.message.content', ''));
        } catch (\Throwable $e) {
            Log::error('AI report narrative exception', ['error' => $e->getMessage()]);
            return "Report generated with {$total} total calls and {$completed} completed.";
        }
    }

    private function fallbackAnalysis(string $transcript): array
    {
        $words   = str_word_count(strtolower($transcript), 1);
        $preview = mb_substr($transcript, 0, 200);

        return [
            'summary'  => "Call transcript recorded. Preview: {$preview}...",
            'insights' => [
                'sentiment'    => 'neutral',
                'keywords'     => array_unique(array_slice($words, 0, 5)),
                'action_items' => ['Review call recording for follow-up actions.'],
                'outcome'      => 'unknown',
            ],
        ];
    }

    private function encodeForPrompt(mixed $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
