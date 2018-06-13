<?php
namespace GoatPen\Observers;

use GoatPen\Utilities\ModelDiff;
use GoatPen\{Influencer, Revision, User};

class UserObserver
{
    public function created(User $user)
    {
        Revision::log($user, 'created_at');
    }

    public function updated(User $user)
    {
        foreach (ModelDiff::asArray($user) as $key => $changes) {
            Revision::log($user, $key, $changes['old'], $changes['new']);
        }
    }

    public function deleting(User $user)
    {
		/*j
        foreach (Influencer::query()->where('user_id', '=', $user->getKey())->get() as $influencer) {
            $influencer->user()->dissociate();
            $influencer->save();
        }

        foreach (Influencer::query()->where('secondary_user_id', '=', $user->getKey())->get() as $influencer) {
            $influencer->secondaryUser()->dissociate();
            $influencer->save();
        }
		 */

        if ($token = $user->token) {
            $token->delete();
        }
    }

    public function deleted(User $user)
    {
        Revision::log($user, 'deleted_at');
    }
}
