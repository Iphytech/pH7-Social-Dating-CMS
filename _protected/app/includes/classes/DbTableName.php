<?php
/**
 * @author           Pierre-Henry Soria <hello@ph7cms.com>
 * @copyright        (c) 2018, Pierre-Henry Soria. All Rights Reserved.
 * @license          GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package          PH7 / App / Include / Class
 */

namespace PH7;

class DbTableName
{
    const ADMIN = 'admins';
    const MEMBER = 'members';
    const MEMBERSHIP = 'memberships';
    const AFFILIATE = 'affiliates';
    const SUBSCRIBER = 'subscribers';
    const MEMBER_INFO = 'members_info';
    const AFFILIATE_INFO = 'affiliates_info';
    const MEMBER_PRIVACY = 'members_privacy';
    const MEMBER_NOTIFICATION = 'members_notifications';
    const BLOCK_IP = 'block_ip';
    const AD = 'ads';
    const AD_AFFILIATE = 'ads_affiliates';
    const PICTURE = 'pictures';
    const VIDEO = 'videos';
    const ALBUM_PICTURE = 'albums_pictures';
    const ALBUM_VIDEO = 'albums_videos';
    const ANALYTIC_API = 'analytics_api';
    const BLOG = 'blogs';
    const BLOG_CATEGORY = 'blogs_categories';
    const BLOG_DATA_CATEGORY = 'blogs_data_categories';
    const NOTE = 'notes';
    const NOTE_CATEGORY = 'notes_categories';
    const NOTE_DATA_CATEGORY = 'notes_data_categories';
    const COMMENT_BLOG = 'comments_blog';
    const COMMENT_NOTE = 'comments_note';
    const COMMENT_PICTURE = 'comments_picture';
    const COMMENT_VIDEO = 'comments_video';
    const COMMENT_GAME = 'comments_game';
    const COMMENT_PROFILE = 'comments_profile';
    const FORUM = 'forums';
    const FORUM_CATEGORY = 'forums_categories';
    const FORUM_TOPIC = 'forums_topics';
    const FORUM_MESSAGE = 'forums_messages';

    const USER_TABLES = [
        self::ADMIN,
        self::MEMBER,
        self::AFFILIATE
    ];
}