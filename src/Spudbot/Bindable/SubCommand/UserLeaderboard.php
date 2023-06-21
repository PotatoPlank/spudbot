<?php
declare(strict_types=1);

namespace Spudbot\Bindable\SubCommand;


use Discord\Parts\Interactions\Interaction;
use Spudbot\Interface\ISubCommand;
use Spudbot\Model\Member;
use Spudbot\Repository\SQL\MemberRepository;

class UserLeaderboard extends ISubCommand
{
    protected string $subCommand = 'leaderboard';
    public function execute(?Interaction $interaction): void
    {
        /**
         * @var MemberRepository $repository
         */
        $repository = $this->spud->getMemberRepository();
        $builder = $this->spud->getSimpleResponseBuilder();
        $members = $repository->getAll();

        $maximumUsers = $_ENV['LEADERBOARD_LENGTH'];
        $leaderboard = [];
        /**
         * @var Member $member
         */
        foreach ($members as $member) {
            if($member->getGuild()->getDiscordId() === $interaction->guild_id){
                if(count($leaderboard) >= $maximumUsers)
                {
                    foreach ($leaderboard as $key => $totalComments){
                        if($member->getTotalComments() > $totalComments){
                            unset($leaderboard[$key]);
                            $leaderboard[$member->getDiscordId()] = $member->getTotalComments();
                            arsort($leaderboard);
                        }
                    }
                }else{
                    $leaderboard[$member->getDiscordId()] = $member->getTotalComments();
                    if(count($leaderboard) === $maximumUsers){
                        arsort($leaderboard);
                    }
                }
            }
        }

        $title = 'User Leaderboard';
        $message = '';
        if(!empty($leaderboard)){
            $position = 1;
            foreach ($leaderboard as $memberId => $comments)
            {
                $message .= "{$position}) <@{$memberId}> has made {$comments} comments." . PHP_EOL;

                $position++;
            }
        }else{
            $message = 'Unable to retrieve leaderboard.';
        }



        $builder->setTitle($title);
        $builder->setDescription($message);

        $interaction->respondWithMessage($builder->getEmbeddedMessage());
    }
}