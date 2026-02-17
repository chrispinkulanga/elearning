<?php

namespace App\Services;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Support\Facades\DB;

class PollService
{
    public function createPoll($topicId, $pollData)
    {
        return DB::transaction(function () use ($topicId, $pollData) {
            $poll = Poll::create([
                'topic_id' => $topicId,
                'question' => $pollData['question'],
                'is_multiple_choice' => $pollData['is_multiple_choice'] ?? false,
                'is_active' => true,
                'expires_at' => isset($pollData['expires_at']) ? \Carbon\Carbon::parse($pollData['expires_at']) : null,
            ]);

            // Create poll options
            foreach ($pollData['options'] as $index => $optionText) {
                if (trim($optionText)) {
                    PollOption::create([
                        'poll_id' => $poll->id,
                        'option_text' => trim($optionText),
                        'sort_order' => $index,
                    ]);
                }
            }

            return $poll->load('options');
        });
    }

    public function vote($pollId, $optionId, $userId)
    {
        $poll = Poll::with('options')->findOrFail($pollId);
        
        // Check if poll is active and not expired
        if (!$poll->is_active || $poll->isExpired()) {
            throw new \Exception('Poll is not active or has expired');
        }

        // Check if user has already voted for this option
        $existingVote = PollVote::where('user_id', $userId)
            ->where('poll_id', $pollId)
            ->where('poll_option_id', $optionId)
            ->first();

        if ($existingVote) {
            throw new \Exception('You have already voted for this option');
        }

        // If not multiple choice, check if user has voted for any option in this poll
        if (!$poll->is_multiple_choice) {
            $existingVoteInPoll = PollVote::where('user_id', $userId)
                ->where('poll_id', $pollId)
                ->first();

            if ($existingVoteInPoll) {
                throw new \Exception('You have already voted in this poll');
            }
        }

        // Create the vote
        PollVote::create([
            'user_id' => $userId,
            'poll_id' => $pollId,
            'poll_option_id' => $optionId,
        ]);

        return $this->getPollResults($pollId);
    }

    public function getPollResults($pollId)
    {
        $poll = Poll::with(['options.votes'])->findOrFail($pollId);
        
        $results = [
            'poll' => $poll,
            'total_votes' => $poll->total_votes,
            'options' => $poll->options->map(function ($option) use ($poll) {
                return [
                    'id' => $option->id,
                    'text' => $option->option_text,
                    'votes' => $option->vote_count,
                    'percentage' => $poll->total_votes > 0 ? round(($option->vote_count / $poll->total_votes) * 100, 1) : 0,
                ];
            }),
        ];

        return $results;
    }

    public function getUserVote($pollId, $userId)
    {
        return PollVote::where('poll_id', $pollId)
            ->where('user_id', $userId)
            ->with('option')
            ->get();
    }

    public function updatePoll($pollId, $pollData)
    {
        $poll = Poll::findOrFail($pollId);
        
        $poll->update([
            'question' => $pollData['question'],
            'is_multiple_choice' => $pollData['is_multiple_choice'] ?? false,
            'expires_at' => isset($pollData['expires_at']) ? \Carbon\Carbon::parse($pollData['expires_at']) : null,
        ]);

        // Update options if provided
        if (isset($pollData['options'])) {
            // Delete existing options
            $poll->options()->delete();
            
            // Create new options
            foreach ($pollData['options'] as $index => $optionText) {
                if (trim($optionText)) {
                    PollOption::create([
                        'poll_id' => $poll->id,
                        'option_text' => trim($optionText),
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        return $poll->load('options');
    }

    public function deletePoll($pollId)
    {
        $poll = Poll::findOrFail($pollId);
        $poll->delete();
        return true;
    }
}
