<?php

namespace Platform\Training\Traits;

use Platform\Crm\Models\CrmContact;
use Platform\Crm\Models\CrmContactLink;

trait HasCrmContact
{
    public function crmContactLinks()
    {
        return $this->morphMany(CrmContactLink::class, 'linkable');
    }

    public function getContact(): ?CrmContact
    {
        return $this->crmContactLinks->first()?->contact;
    }

    public function getFullNameAttribute(): ?string
    {
        return $this->getContact()?->full_name;
    }

    public function getEmailAttribute(): ?string
    {
        $contact = $this->getContact();
        if (!$contact) {
            return null;
        }

        return $contact->emailAddresses()
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->first()
            ?->email_address;
    }

    public function getPhoneAttribute(): ?string
    {
        $contact = $this->getContact();
        if (!$contact) {
            return null;
        }

        return $contact->phoneNumbers()
            ->where('is_active', true)
            ->orderByDesc('is_primary')
            ->first()
            ?->international;
    }

    public function getCompanyNameAttribute(): ?string
    {
        $contact = $this->getContact();
        if (!$contact) {
            return null;
        }

        return $contact->companies()
            ->wherePivot('is_primary', true)
            ->first()
            ?->name;
    }

    public function hasContact(): bool
    {
        return $this->crmContactLinks()->exists();
    }

    public function linkContact(CrmContact $contact): void
    {
        if ($this->crmContactLinks()->where('contact_id', $contact->id)->exists()) {
            return;
        }

        CrmContactLink::create([
            'contact_id' => $contact->id,
            'linkable_id' => $this->id,
            'linkable_type' => get_class($this),
            'team_id' => $this->team_id,
            'created_by_user_id' => auth()->id(),
        ]);
    }

    public function unlinkContact(): void
    {
        $this->crmContactLinks()->delete();
    }
}
