<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta — BotTrade</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-gray-950 flex items-center justify-center">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <span class="text-green-400 text-3xl font-bold">&#9650; BotTrade</span>
            <p class="text-gray-400 text-sm mt-1">Binance Spot Bot</p>
        </div>

        <div class="bg-gray-900 border border-gray-700 rounded-2xl p-8">
            <h1 class="text-white text-xl font-semibold mb-6">Criar conta</h1>

            @if ($errors->any())
                <div class="bg-red-950 border border-red-800 text-red-400 text-sm px-4 py-3 rounded-lg mb-5">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nome</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white text-sm focus:border-green-500 focus:outline-none placeholder-gray-600"
                        placeholder="Seu nome"
                    />
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Email</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white text-sm focus:border-green-500 focus:outline-none placeholder-gray-600"
                        placeholder="seu@email.com"
                    />
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Senha</label>
                    <input
                        type="password"
                        name="password"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white text-sm focus:border-green-500 focus:outline-none"
                        placeholder="Mínimo 8 caracteres"
                    />
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Confirmar senha</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white text-sm focus:border-green-500 focus:outline-none"
                        placeholder="Repita a senha"
                    />
                </div>
                <button
                    type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 rounded-lg text-sm transition mt-2"
                >
                    Criar conta
                </button>
            </form>

            <p class="text-center text-gray-400 text-sm mt-6">
                Já tem conta?
                <a href="{{ route('login') }}" class="text-green-400 hover:text-green-300 transition">Entrar</a>
            </p>
        </div>
    </div>
</body>
</html>
