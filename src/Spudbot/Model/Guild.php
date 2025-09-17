<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2023-2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

declare(strict_types=1);

namespace Spudbot\Model;

use BadMethodCallException;
use Carbon\CarbonTimeZone;
use Discord\Discord;
use Discord\Parts\Channel\Channel;

class Guild extends AbstractModel
{
    public const BOT_LOG_CHANNEL = 'announce';
    public const PUBLIC_LOG_CHANNEL = 'public';
    public const MOD_ALERT_CHANNEL = 'mod_alert';
    public const INTRO_CHANNEL = 'introductions';
    public const MARKETPLACE_CHANNEL = 'marketplace';
    public const VERIFIED_CHANNEL = 'verified';
    public const string MEMBER_COUNT_CATEGORY_NAME = 'Member Count 📈';
    public ?string $verifiedMembersChannelId = null;
    public ?string $verifiedMembersRoleId = null;
    public ?string $tenuredMemberRoleId = null;
    public ?string $memberCountChannelId = null;
    public CarbonTimeZone $timeZone;
    public string $discordId;
    public ?string $channelAnnounceId = null;
    public ?string $channelThreadAnnounceId = null;
    public ?string $channelPublicLogId = null;
    public ?string $channelThreadPublicLogId = null;
    public ?string $channelModAlertId = null;
    public ?string $channelThreadModAlertId = null;
    public ?string $channelIntroductionId = null;
    public ?string $channelThreadIntroductionId = null;
    public ?string $channelMarketplaceId = null;
    public ?string $channelThreadMarketplaceId = null;

    public function __construct()
    {
        $this->timeZone = new CarbonTimeZone('America/New_York');
    }

    public static function locateMemberCountCategoryId(\Discord\Parts\Guild\Guild $guild): string
    {
        $categoryId = $guild->channels->get('name', static::MEMBER_COUNT_CATEGORY_NAME)?->id;

        if(!$categoryId){
            $category = new Channel($guild->getDiscord());
            $category->type = Channel::TYPE_GUILD_CATEGORY;
            $category->name = static::MEMBER_COUNT_CATEGORY_NAME;
            $guild->channels->save($category);
            $categoryId = $category->id;
        }
        return $categoryId;
    }

    public static function locateMemberCountChannel(\Discord\Parts\Guild\Guild $guild): Channel
    {
        $categoryId = static::locateMemberCountCategoryId($guild);

        $channel = $guild->channels->find(function (Channel $channel) use ($categoryId){
            return $channel->parent_id === $categoryId && str_contains($channel->name, 'Member Count');
        });

        if (!$channel) {
            $everyoneRole = $guild->roles->get('name', '@everyone');
            $channel = new Channel($guild->getDiscord());
            $channel->type = Channel::TYPE_GUILD_VOICE;
            $channel->name = static::getMemberCountChannelName($guild->member_count);
            if ($everyoneRole) {
                $channel->setPermissions($everyoneRole, [
                    'view_channel',
                ], [
                    'connect',
                ]);
            }
            $channel->parent_id = $categoryId;
            $guild->channels->save($channel);
        }

        return $channel;
    }

    public function setChannelMemberCount(\Discord\Parts\Guild\Guild $guild): void
    {
        if(!$this->memberCountChannelId){
            throw new \RuntimeException('Member count channel id not set.');
        }

        $channel = $guild->channels->get('id', $this->memberCountChannelId);
        if(!$channel){
            throw new \RuntimeException('Member count channel not found.');
        }
        $channel->name = static::getMemberCountChannelName($guild->member_count);
        $guild->channels->save($channel);
    }

    public function getChannelThreadPart(
        string $logType,
        \Discord\Parts\Guild\Guild $guild
    ): Channel|\Discord\Parts\Thread\Thread {
        switch ($logType) {
            case self::BOT_LOG_CHANNEL:
                $channelId = $this->channelAnnounceId;
                $threadId = $this->channelThreadAnnounceId;
                break;
            case self::INTRO_CHANNEL:
                $channelId = $this->channelIntroductionId;
                $threadId = $this->channelThreadIntroductionId;
                break;
            case self::MARKETPLACE_CHANNEL:
                $channelId = $this->channelMarketplaceId;
                $threadId = $this->channelThreadMarketplaceId;
                break;
            case self::MOD_ALERT_CHANNEL:
                $channelId = $this->channelModAlertId;
                $threadId = $this->channelThreadModAlertId;
                break;
            case self::PUBLIC_LOG_CHANNEL:
                $channelId = $this->channelPublicLogId;
                $threadId = $this->channelThreadPublicLogId;
                break;
            case self::VERIFIED_CHANNEL:
                $channelId = $this->verifiedMembersChannelId;
                $threadId = null;
                break;
            default:
                throw new \InvalidArgumentException("$logType is not a valid channel type.");
        }

        $outputPart = $guild->channels->get('id', $channelId);
        if (!$outputPart) {
            throw new BadMethodCallException(
                "Failed locating channel {$channelId} for {$guild->id}."
            );
        }
        if (!empty($threadId)) {
            $outputPart = $outputPart->threads->get('id', $threadId);
            if (!$outputPart) {
                throw new BadMethodCallException(
                    "Failed locating thread {$threadId} in channel {$channelId} for {$guild->id}"
                );
            }
        }
        return $outputPart;
    }

    public function hasPublicModLog(): bool
    {
        return !empty($this->channelPublicLogId);
    }

    public function hasMarketplace(): bool
    {
        return !empty($this->channelMarketplaceId);
    }

    public function hasIntroductionsChannel(): bool
    {
        return !empty($this->channelIntroductionId);
    }

    public function hasVerifiedRole(): bool
    {
        return !empty($this->verifiedMembersRoleId);
    }

    public function getDiscordId(): string
    {
        return $this->discordId;
    }

    private static function getMemberCountChannelName(string|int $count = 'x'): string
    {
        return "Member Count: $count";
    }
}
