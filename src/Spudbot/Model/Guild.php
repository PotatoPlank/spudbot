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
    private string $discordId;
    private ?string $channelAnnounceId = null;
    private ?string $channelThreadAnnounceId = null;
    private ?string $channelPublicLogId = null;
    private ?string $channelThreadPublicLogId = null;
    private ?string $channelModAlertId = null;
    private ?string $channelThreadModAlertId = null;
    private ?string $channelIntroductionId = null;
    private ?string $channelThreadIntroductionId = null;
    private ?string $channelMarketplaceId = null;
    private ?string $channelThreadMarketplaceId = null;
    private ?string $verifiedMembersChannelId = null;
    private ?string $verifiedMembersRoleId = null;
    private ?string $tenuredMemberRoleId = null;
    private CarbonTimeZone $timeZone;

    public function __construct()
    {
        $this->timeZone = new CarbonTimeZone('America/New_York');
    }

    public static function updateMemberCount(\Discord\Parts\Guild\Guild $guild, Discord $discord): void
    {
        $categoryName = 'Member Count 📈';

        $memberCount = $guild->member_count;
        $category = $guild->channels->get('name', $categoryName);
        if (!$category) {
            $category = new Channel($discord);
            $category->type = Channel::TYPE_GUILD_CATEGORY;
            $category->name = $categoryName;
            $guild->channels->save($category);
        }
        $channel = $guild->channels->get('parent_id', $category->id);
        if (!$channel) {
            $everyoneRole = $guild->roles->get('name', '@everyone');
            $channel = new Channel($discord);
            $channel->type = Channel::TYPE_GUILD_VOICE;
            $channel->name = "Member Count: {$memberCount}";
            if ($everyoneRole) {
                $channel->setPermissions($everyoneRole, [
                    'view_channel',
                ], [
                    'connect',
                ]);
            }
            $channel->parent_id = $category->id;
        } else {
            $channel->name = "Member Count: {$memberCount}";
        }

        $guild->channels->save($channel);
    }

    public function getTenuredMemberRoleId(): ?string
    {
        return $this->tenuredMemberRoleId;
    }

    public function setTenuredMemberRoleId(?string $tenuredMemberRoleId): void
    {
        $this->tenuredMemberRoleId = $tenuredMemberRoleId;
    }

    public function getVerifiedMembersChannelId(): ?string
    {
        return $this->verifiedMembersChannelId;
    }

    public function setVerifiedMembersChannelId(?string $verifiedMembersChannelId): void
    {
        $this->verifiedMembersChannelId = $verifiedMembersChannelId;
    }

    public function getVerifiedMembersRoleId(): ?string
    {
        return $this->verifiedMembersRoleId;
    }

    public function setVerifiedMembersRoleId(?string $verifiedMembersRoleId): void
    {
        $this->verifiedMembersRoleId = $verifiedMembersRoleId;
    }

    public function getOutputLocationId(): ?string
    {
        if (!empty($this->getChannelThreadAnnounceId())) {
            return $this->getChannelThreadAnnounceId();
        }
        return $this->getChannelAnnounceId();
    }

    public function getChannelThreadAnnounceId(): ?string
    {
        return $this->channelThreadAnnounceId;
    }

    public function setChannelThreadAnnounceId(?string $threadId): void
    {
        $this->channelThreadAnnounceId = $threadId;
    }

    public function getChannelAnnounceId(): ?string
    {
        return $this->channelAnnounceId;
    }

    public function setChannelAnnounceId(?string $channelId): void
    {
        $this->channelAnnounceId = $channelId;
    }

    public function getChannelThreadPart(
        string $logType,
        \Discord\Parts\Guild\Guild $guild
    ): Channel|\Discord\Parts\Thread\Thread {
        switch ($logType) {
            case self::BOT_LOG_CHANNEL:
                $channelId = $this->getChannelAnnounceId();
                $threadId = $this->getChannelThreadAnnounceId();
                break;
            case self::INTRO_CHANNEL:
                $channelId = $this->getChannelIntroductionId();
                $threadId = $this->getChannelThreadIntroductionId();
                break;
            case self::MARKETPLACE_CHANNEL:
                $channelId = $this->getChannelMarketplaceId();
                $threadId = $this->getChannelThreadMarketplaceId();
                break;
            case self::MOD_ALERT_CHANNEL:
                $channelId = $this->getChannelModAlertId();
                $threadId = $this->getChannelThreadModAlertId();
                break;
            case self::PUBLIC_LOG_CHANNEL:
                $channelId = $this->getChannelPublicLogId();
                $threadId = $this->getChannelThreadPublicLogId();
                break;
            case self::VERIFIED_CHANNEL:
                $channelId = $this->verifiedMembersChannelId;
                $threadId = null;
            default:
                throw new \InvalidArgumentException("$logType is not a valid channel type.");
        }

        $outputPart = $guild->channels->get('id', $channelId);
        if (!$outputPart) {
            throw new BadMethodCallException(
                "Failed locating channel {$channelId} for {$guild->id}."
            );
        }
        if (!empty($this->getChannelThreadAnnounceId())) {
            $outputPart = $outputPart->threads->get('id', $threadId);
            if (!$outputPart) {
                throw new BadMethodCallException(
                    "Failed locating thread {$threadId} in channel {$channelId} for {$guild->id}"
                );
            }
        }
        return $outputPart;
    }

    public function getChannelIntroductionId(): ?string
    {
        return $this->channelIntroductionId;
    }

    public function setChannelIntroductionId(?string $channelIntroductionId): void
    {
        $this->channelIntroductionId = $channelIntroductionId;
    }

    public function getChannelThreadIntroductionId(): ?string
    {
        return $this->channelThreadIntroductionId;
    }

    public function setChannelThreadIntroductionId(?string $channelThreadIntroductionId): void
    {
        $this->channelThreadIntroductionId = $channelThreadIntroductionId;
    }

    public function getChannelMarketplaceId(): ?string
    {
        return $this->channelMarketplaceId;
    }

    public function setChannelMarketplaceId(?string $channelMarketplaceId): void
    {
        $this->channelMarketplaceId = $channelMarketplaceId;
    }

    public function getChannelThreadMarketplaceId(): ?string
    {
        return $this->channelThreadMarketplaceId;
    }

    public function setChannelThreadMarketplaceId(?string $channelThreadMarketplaceId): void
    {
        $this->channelThreadMarketplaceId = $channelThreadMarketplaceId;
    }

    public function getChannelModAlertId(): ?string
    {
        return $this->channelModAlertId;
    }

    public function setChannelModAlertId(?string $channelModAlertId): void
    {
        $this->channelModAlertId = $channelModAlertId;
    }

    public function getChannelThreadModAlertId(): ?string
    {
        return $this->channelThreadModAlertId;
    }

    public function setChannelThreadModAlertId(?string $channelThreadModAlertId): void
    {
        $this->channelThreadModAlertId = $channelThreadModAlertId;
    }

    public function getChannelPublicLogId(): ?string
    {
        return $this->channelPublicLogId;
    }

    public function setChannelPublicLogId(?string $channelPublicLogId): void
    {
        $this->channelPublicLogId = $channelPublicLogId;
    }

    public function getChannelThreadPublicLogId(): ?string
    {
        return $this->channelThreadPublicLogId;
    }

    public function setChannelThreadPublicLogId(?string $channelThreadPublicLogId): void
    {
        $this->channelThreadPublicLogId = $channelThreadPublicLogId;
    }

    /**
     * @return CarbonTimeZone
     */
    public function getTimeZone(): CarbonTimeZone
    {
        return $this->timeZone;
    }

    /**
     * @param CarbonTimeZone $timeZone
     */
    public function setTimeZone(CarbonTimeZone $timeZone): void
    {
        $this->timeZone = $timeZone;
    }

    public function getDiscordId(): string
    {
        return $this->discordId;
    }

    public function setDiscordId(string $discordId): void
    {
        $this->discordId = $discordId;
    }
}
