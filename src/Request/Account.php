<?php

namespace InstagramAPI\Request;

use InstagramAPI\Response;

/**
 * Account-related functions, such as profile editing and security.
 */
class Account extends RequestCollection
{
    /**
     * Get details about the currently logged in account.
     *
     * Also try People::getSelfInfo() instead, for some different information.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     *
     * @see People::getSelfInfo()
     */
    public function getCurrentUser()
    {
        return $this->ig->request('accounts/current_user/')
            ->addParam('edit', true)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Edit your biography.
     *
     * You are able to add `@mentions` and `#hashtags` to your biography, but
     * be aware that Instagram disallows certain web URLs and shorteners.
     *
     * Also keep in mind that anyone can read your biography (even if your
     * account is private).
     *
     * WARNING: Remember to also call `editProfile()` *after* using this
     * function, so that you act like the real app!
     *
     * @param string $biography Biography text. Use "" for nothing.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     *
     * @see Account::editProfile() should be called after this function!
     */
    public function setBiography(
        $biography)
    {
        if (!is_string($biography) || strlen($biography) > 150) {
            throw new InvalidArgumentException('Please provide a 0 to 150 character string as biography.');
        }

        return $this->ig->request('accounts/set_biography/')
            ->addPost('raw_text', $biography)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Edit your profile.
     *
     * Warning: You must provide ALL parameters to this function. The values
     * which you provide will overwrite all current values on your profile.
     * You can use getCurrentUser() to see your current values first.
     *
     * @param string      $url         Website URL. Use "" for nothing.
     * @param string      $phone       Phone number. Use "" for nothing.
     * @param string      $name        Full name. Use "" for nothing.
     * @param string      $biography   Biography text. Use "" for nothing.
     * @param string      $email       Email. Required!
     * @param int         $gender      Gender (1 = male, 2 = female, 3 = unknown). Required!
     * @param null|string $newUsername (optional) Rename your account to a new username,
     *                                 which you've already verified with checkUsername().
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     *
     * @see Account::getCurrentUser() to get your current account details.
     * @see Account::checkUsername() to verify your new username first.
     */
    public function editProfile(
        $url,
        $phone,
        $name,
        $biography,
        $email,
        $gender,
        $newUsername = null)
    {
        // We must mark the profile for editing before doing the main request.
        $this->ig->request('accounts/current_user/')
            ->addParam('edit', true)
            ->getResponse(new Response\UserInfoResponse());

        // Determine the desired username value.
        $username = is_string($newUsername) && strlen($newUsername) > 0
                  ? $newUsername
                  : $this->ig->username;

        return $this->ig->request('accounts/edit_profile/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('external_url', $url)
            ->addPost('phone_number', $phone)
            ->addPost('username', $username)
            ->addPost('first_name', $name)
            ->addPost('biography', $biography)
            ->addPost('email', $email)
            ->addPost('gender', $gender)
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Set your account's name and phone.
     *
     * @param string $name  Your name.
     * @param string $phone Your phone number (optional).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function setNameAndPhone(
        $name = '',
        $phone = '')
    {
        return $this->ig->request('accounts/set_phone_and_name/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('first_name', $name)
            ->addPost('phone_number', $phone)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Changes your account's profile picture.
     *
     * @param string $photoFilename The photo filename.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function changeProfilePicture(
        $photoFilename)
    {
        return $this->ig->request('accounts/change_profile_picture/')
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addFile('profile_pic', $photoFilename, 'profile_pic')
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Remove your account's profile picture.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function removeProfilePicture()
    {
        return $this->ig->request('accounts/remove_profile_picture/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Sets your account to public.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function setPublic()
    {
        return $this->ig->request('accounts/set_public/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Sets your account to private.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function setPrivate()
    {
        return $this->ig->request('accounts/set_private/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Check if an Instagram username is available (not already registered).
     *
     * Use this before trying to rename your Instagram account,
     * to be sure that the new username is available.
     *
     * @param string $username Instagram username to check.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CheckUsernameResponse
     *
     * @see Account::editProfile() to rename your account.
     */
    public function checkUsername(
        $username)
    {
        return $this->ig->request('users/check_username/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('username', $username)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uid', $this->ig->account_id)
            ->getResponse(new Response\CheckUsernameResponse());
    }

    /**
     * Get account spam filter status.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterResponse
     */
    public function getCommentFilter()
    {
        return $this->ig->request('accounts/get_comment_filter/')
            ->getResponse(new Response\CommentFilterResponse());
    }

    /**
     * Set account spam filter status (on/off).
     *
     * @param int $config_value Whether spam filter is on (0 or 1).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterSetResponse
     */
    public function setCommentFilter(
        $config_value)
    {
        return $this->ig->request('accounts/set_comment_filter/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('config_value', $config_value)
            ->getResponse(new Response\CommentFilterSetResponse());
    }

    /**
     * Get whether the comment category filter is disabled.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentCategoryFilterResponse
     */
    public function getCommentCategoryFilterDisabled()
    {
        return $this->ig->request('accounts/get_comment_category_filter_disabled/')
            ->getResponse(new Response\CommentCategoryFilterResponse());
    }

    /**
     * Get account spam filter keywords.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterKeywordsResponse
     */
    public function getCommentFilterKeywords()
    {
        return $this->ig->request('accounts/get_comment_filter_keywords/')
            ->getResponse(new Response\CommentFilterKeywordsResponse());
    }

    /**
     * Set account spam filter keywords.
     *
     * @param string $keywords List of blocked words, separated by comma.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterSetResponse
     */
    public function setCommentFilterKeywords(
        $keywords)
    {
        return $this->ig->request('accounts/set_comment_filter_keywords/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('keywords', $keywords)
            ->getResponse(new Response\CommentFilterSetResponse());
    }

    /**
     * Change your account's password.
     *
     * @param string $oldPassword Old password.
     * @param string $newPassword New password.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ChangePasswordResponse
     */
    public function changePassword(
        $oldPassword,
        $newPassword)
    {
        return $this->ig->request('accounts/change_password/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('old_password', $oldPassword)
            ->addPost('new_password1', $newPassword)
            ->addPost('new_password2', $newPassword)
            ->getResponse(new Response\ChangePasswordResponse());
    }

    /**
     * Get account security info and backup codes.
     *
     * WARNING: STORE AND KEEP BACKUP CODES IN A SAFE PLACE. THEY ARE EXTREMELY
     *          IMPORTANT! YOU WILL GET THE CODES IN THE RESPONSE. THE BACKUP
     *          CODES LET YOU REGAIN CONTROL OF YOUR ACCOUNT IF YOU LOSE THE
     *          PHONE NUMBER! WITHOUT THE CODES, YOU RISK LOSING YOUR ACCOUNT!
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AccountSecurityInfoResponse
     *
     * @see Account::enableTwoFactorSMS()
     */
    public function getSecurityInfo()
    {
        return $this->ig->request('accounts/account_security_info/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\AccountSecurityInfoResponse());
    }

    /**
     * Request that Instagram enables two factor SMS authentication.
     *
     * The SMS will have a verification code for enabling two factor SMS
     * authentication. You must then give that code to enableTwoFactorSMS().
     *
     * @param string $phoneNumber Phone number with country code. Format: +34123456789.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendTwoFactorEnableSMSResponse
     *
     * @see Account::enableTwoFactorSMS()
     */
    public function sendTwoFactorEnableSMS(
        $phoneNumber)
    {
        $cleanNumber = '+'.preg_replace('/[^0-9]/', '', $phoneNumber);

        return $this->ig->request('accounts/send_two_factor_enable_sms/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('phone_number', $cleanNumber)
            ->getResponse(new Response\SendTwoFactorEnableSMSResponse());
    }

    /**
     * Enable Two Factor authentication.
     *
     * WARNING: STORE AND KEEP BACKUP CODES IN A SAFE PLACE. THEY ARE EXTREMELY
     *          IMPORTANT! YOU WILL GET THE CODES IN THE RESPONSE. THE BACKUP
     *          CODES LET YOU REGAIN CONTROL OF YOUR ACCOUNT IF YOU LOSE THE
     *          PHONE NUMBER! WITHOUT THE CODES, YOU RISK LOSING YOUR ACCOUNT!
     *
     * @param string $phoneNumber      Phone number with country code. Format: +34123456789.
     * @param string $verificationCode The code sent to your phone via `Account::sendTwoFactorEnableSMS()`.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AccountSecurityInfoResponse
     *
     * @see Account::sendTwoFactorEnableSMS()
     * @see Account::getSecurityInfo()
     */
    public function enableTwoFactorSMS(
        $phoneNumber,
        $verificationCode)
    {
        $cleanNumber = '+'.preg_replace('/[^0-9]/', '', $phoneNumber);

        $response = $this->ig->request('accounts/enable_sms_two_factor/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('phone_number', $cleanNumber)
            ->addPost('verification_code', $verificationCode)
            ->getResponse(new Response\EnableTwoFactorSMSResponse());

        return $this->getSecurityInfo();
    }

    /**
     * Disable Two Factor authentication.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DisableTwoFactorSMSResponse
     */
    public function disableTwoFactorSMS()
    {
        return $this->ig->request('accounts/disable_sms_two_factor/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\DisableTwoFactorSMSResponse());
    }

    /**
     * Request a new security code SMS for a Two Factor login account.
     *
     * NOTE: You should first attempt to `login()` which will automatically send
     * you a two factor SMS. This function is just for asking for a new SMS if
     * the old code has expired.
     *
     * NOTE: Instagram can only send you a new code every 60 seconds.
     *
     * @param string $username            Your Instagram username.
     * @param string $twoFactorIdentifier Two factor identifier, obtained in
     *                                    `login()` response.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TwoFactorLoginSMSResponse
     */
    public function sendTwoFactorLoginSMS(
        $username,
        $twoFactorIdentifier)
    {
        return $this->ig->request('accounts/send_two_factor_login_sms/')
            ->addPost('two_factor_identifier', $twoFactorIdentifier)
            ->addPost('username', $username)
            ->addPost('device_id', $this->ig->device_id)
            ->addPost('guid', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\TwoFactorLoginSMSResponse());
    }

    /**
     * Get presence status.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PresenceStatusResponse
     */
    public function getPresenceStatus()
    {
        return $this->ig->request('accounts/get_presence_disabled/')
            ->setSignedGet(true)
            ->getResponse(new Response\PresenceStatusResponse());
    }

    /**
     * Enable presence.
     *
     * Allow accounts you follow and anyone you message to see when you were
     * last active on Instagram apps.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function enablePresence()
    {
        return $this->ig->request('accounts/set_presence_disabled/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('disabled', '0')
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Disable presence.
     *
     * You won't be able to see the activity status of other accounts.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PresenceStatusResponse
     */
    public function disablePresence()
    {
        return $this->ig->request('accounts/set_presence_disabled/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('disabled', '1')
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Tell Instagram to send you a message to verify your email address.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendConfirmEmailResponse
     */
    public function sendConfirmEmail()
    {
        return $this->ig->request('accounts/send_confirm_email/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('send_source', 'edit_profile')
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\SendConfirmEmailResponse());
    }

    /**
     * Tell Instagram to send you an SMS code to verify your phone number.
     *
     * @param string $phoneNumber Phone number with country code. Format: +34123456789.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendSMSCodeResponse
     */
    public function sendSMSCode(
        $phoneNumber)
    {
        $cleanNumber = '+'.preg_replace('/[^0-9]/', '', $phoneNumber);

        return $this->ig->request('accounts/send_sms_code/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('phone_number', $cleanNumber)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\SendSMSCodeResponse());
    }

    /**
     * Submit the SMS code you received to verify your phone number.
     *
     * @param string $phoneNumber      Phone number with country code. Format: +34123456789.
     * @param string $verificationCode The code sent to your phone via `Account::sendSMSCode()`.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\VerifySMSCodeResponse
     *
     * @see Account::sendSMSCode()
     */
    public function verifySMSCode(
        $phoneNumber,
        $verificationCode)
    {
        $cleanNumber = '+'.preg_replace('/[^0-9]/', '', $phoneNumber);

        return $this->ig->request('accounts/verify_sms_code/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_uid', $this->ig->account_id)
            ->addPost('phone_number', $cleanNumber)
            ->addPost('verification_code', $verificationCode)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\VerifySMSCodeResponse());
    }

    /**
     * Set contact point prefill.
     *
     * @param string $usage Either "prefill" or "auto_confirmation".
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function setContactPointPrefill(
        $usage)
    {
        return $this->ig->request('accounts/contact_point_prefill/')
            ->setNeedsAuth(false)
            ->addPost('phone_id', $this->ig->phone_id)
            ->addPost('usage', $usage)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get account badge notifications for the "Switch account" menu.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\BadgeNotificationsResponse
     */
    public function getBadgeNotifications()
    {
        return $this->ig->request('notifications/badge/')
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('users_ids', $this->ig->account_id)
            ->addPost('device_id', $this->ig->device_id)
            ->getResponse(new Response\BadgeNotificationsResponse());
    }
}
