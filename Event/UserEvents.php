<?php
namespace Rudak\UserBundle\Event;


final class UserEvents
{

	const USER_RECORD                  = 'rudak_user.record';
	const USER_EMAIL_VALIDATION        = 'rudak_user.email_validation';
	const USER_PASSWORD_RECOVERED      = 'rudak_user.password_recovery.success';
	const USER_PASSWORD_RECOVERY_ERROR = 'rudak_user.password_recovery.error';
	const USER_PASSWORD_LOST_REQUEST   = 'rudak_user.password_lost.request';
	const USER_PASSWORD_CHANGE_SUCCESS = 'rudak_user.password_change.success';
	const USER_PASSWORD_CHANGE_ERROR   = 'rudak_user.password_change.error';
	const USER_EMAIL_CHANGE_REQUEST    = 'rudak_user.email_change.request';
	const USER_EMAIL_CHANGE_SUCCESS    = 'rudak_user.email_change.success';
}