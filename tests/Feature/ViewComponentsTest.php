<?php

namespace Tests\Feature;

use Tests\TestCase;

class ViewComponentsTest extends TestCase
{
    public function test_it_renders_reusable_form_fields_with_custom_attributes(): void
    {
        $view = $this->blade('
            <x-label for="title">Título</x-label>
            <x-input id="title" name="title" placeholder="Nombre del libro" class="custom-input" />
            <x-label for="category">Categoría</x-label>
            <x-select id="category" name="category">
                <option value="">Todas</option>
            </x-select>
            <x-date-input id="deadline" name="deadline" />
            <x-checkbox id="available" name="available" value="1" checked />
        ');

        $view->assertSee('for="title"', false)
            ->assertSee('type="text"', false)
            ->assertSee('class="form-control custom-input"', false)
            ->assertSee('class="form-control form-select"', false)
            ->assertSee('id="category" name="category"', false)
            ->assertSee('type="date"', false)
            ->assertSee('type="checkbox"', false)
            ->assertSee('checked', false)
            ->assertSee('.form-label', false)
            ->assertDontSee('var(', false);
    }
}
