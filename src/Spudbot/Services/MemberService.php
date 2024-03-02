<?php
/*
 * This file is a part of the SpudBot Framework.
 * Copyright (c) 2024. PotatoPlank <potatoplank@protonmail.com>
 * The file is subject to the GNU GPLv3 license that is bundled with this source code in LICENSE.md.
 */

namespace Spudbot\Services;

use OutOfBoundsException;
use Spudbot\Model\Member;
use Spudbot\Repositories\MemberRepository;

class MemberService
{
    public function __construct(public MemberRepository $memberRepository, public GuildService $guildService)
    {
    }

    public function findOrCreateWithPart(\Discord\Parts\User\Member $member): Member
    {
        try {
            return $this->memberRepository->findByPart($member);
        } catch (OutOfBoundsException $exception) {
            return $this->memberRepository->save(Member::create([
                'discordId' => $member->id,
                'totalComments' => 0,
                'username' => Member::getUsernameWithPart($member),
                'verifiedBy' => null,
                'guild' => $this->guildService->findWithPart($member->guild),
            ]));
        }
    }

    public function findWithPart(\Discord\Parts\User\Member $member): ?Member
    {
        try {
            return $this->memberRepository->findByPart($member);
        } catch (OutOfBoundsException $exception) {
            return null;
        }
    }

    public function remove(Member $member): bool
    {
        return $this->memberRepository->remove($member);
    }
}
