<?php
namespace GoatPen\Services;

class DeliverablesService
{
    public static function sanitise(array $deliverables): array
    {
        $data = [];

        foreach ($deliverables as $deliverable) {
            if (! empty($deliverable['id']) && ! empty($deliverable['quantity'])) {
                $data[(int) $deliverable['id']] = (float) $deliverable['quantity'];
            }
        }

        return $data;
    }
}
