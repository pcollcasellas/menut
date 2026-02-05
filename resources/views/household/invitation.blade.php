<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl text-bark-800 leading-tight">
            Invitació a una llar
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="p-6 sm:p-8 bg-white border border-cream-200 shadow-soft rounded-2xl">
                <div class="text-center space-y-4">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-forest-100">
                        <svg class="w-8 h-8 text-forest-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>

                    <h3 class="text-lg font-medium text-bark-800">
                        {{ $invitation->inviter->name }} t'ha convidat a unir-te a la seva llar
                    </h3>

                    <p class="text-sm text-bark-600">
                        Si acceptes, compartireu totes les receptes, menús setmanals i plantilles.
                        Les teves receptes i menús actuals es traslladaran a la nova llar.
                    </p>

                    <div class="pt-4 flex items-center justify-center gap-3">
                        <form method="POST" action="{{ route('household.invitation.accept', $invitation->token) }}">
                            @csrf
                            <x-primary-button>
                                Acceptar invitació
                            </x-primary-button>
                        </form>

                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-5 py-2.5 bg-white border border-cream-300 rounded-xl font-semibold text-sm text-bark-700 tracking-wide hover:bg-cream-50 focus:outline-none focus:ring-2 focus:ring-forest-500 focus:ring-offset-2 transition ease-in-out duration-200">
                            No, gràcies
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
