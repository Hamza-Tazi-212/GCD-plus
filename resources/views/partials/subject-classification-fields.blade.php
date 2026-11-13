@foreach (['laboratory' => 'Laboratoire', 'field' => 'Domaine'] as $key => $label)
    <div class="mb-3">
        <x-input-label :for="$key" :value="$label" />
        <x-text-input :id="$key" :name="$key" :value="old($key, isset($subject) ? $subject->{$key} : null)" maxlength="120" />
        <x-input-error :messages="$errors->get($key)" />
    </div>
@endforeach
