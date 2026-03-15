    <div class="ag-login-wrapper">
        <div class="login-card p-5">
            <h2 class="login-title text-center mb-4">Agendamiento</h2>
            <form wire:submit="authenticate">
                {{ $this->form }}
                <div class="mt-4">
                    <button type="submit" class="custom-btn w-full">
                        Iniciar Sesión
                    </button>
                </div>
            </form>
        </div>
    </div>
