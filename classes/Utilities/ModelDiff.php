<?php
namespace GoatPen\Utilities;

class ModelDiff
{
    public static function asArray($object): array
    {
        $changes = [];

        foreach ($object->revisionable as $field) {
            $oldValue = $object->getOriginal($field);
            $newValue = $object->getAttribute($field);

            if (is_bool($newValue)) { // then $newValue will be also
                $oldValue = ($oldValue ? 'true' : 'false');
                $newValue = ($newValue ? 'true' : 'false');
            } elseif (is_string($newValue)) {
                $newValue = preg_replace('#^https?://#', '', $newValue);
            } elseif (is_array($newValue)) {
                $newValue = json_encode($newValue);
            }

            if ($oldValue == $newValue) {
                continue; // loose comparison, so don't log "changing" from '100' to 100
            }

            $changes[$field] = [
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        return $changes;
    }
}
