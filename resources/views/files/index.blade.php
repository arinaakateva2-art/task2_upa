<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Файловый менеджер</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8 text-gray-800">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-md">
        
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h1 class="text-2xl font-bold">Мой Файловый менеджер</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-red-500 hover:underline text-sm font-bold">Выйти</button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="mb-4 text-lg">
            <a href="{{ route('files.index') }}" class="text-blue-500 hover:underline">Главная</a>
            @if($currentFolder)
                / <span class="font-bold">{{ $currentFolder->name }}</span>
            @endif
        </div>

        <form action="{{ route('files.index') }}" method="GET" class="mb-6 flex gap-2">
            @if($folderId)
                <input type="hidden" name="folder" value="{{ $folderId }}">
            @endif
            <input type="text" name="search" placeholder="Поиск файлов по имени..." value="{{ request('search') }}" class="border p-2 rounded w-full">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Искать</button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="p-4 bg-gray-50 rounded border">
                <h2 class="font-bold mb-2 text-gray-700">Загрузить файл (макс. 5 МБ)</h2>
                <form action="{{ route('files.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ $folderId }}">
                    <input type="file" name="file" required class="border p-1 bg-white">
                    <input type="text" name="tags" placeholder="Теги (через запятую)" class="border p-1 rounded">
                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Загрузить</button>
                </form>
            </div>

            <div class="p-4 bg-gray-50 rounded border">
                <h2 class="font-bold mb-2 text-gray-700">Создать папку</h2>
                <form action="{{ route('folders.store') }}" method="POST" class="flex flex-col gap-2">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $folderId }}">
                    <input type="text" name="name" placeholder="Название папки" required class="border p-1 rounded">
                    <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Создать</button>
                </form>
            </div>
        </div>

        <h2 class="font-bold text-xl mb-3 border-b pb-2">Папки</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            @foreach($folders as $folder)
                <div class="border p-3 rounded flex justify-between items-center bg-gray-50 hover:bg-gray-100">
                    <a href="{{ route('files.index', ['folder' => $folder->id]) }}" class="text-blue-600 font-medium truncate">📁 {{ $folder->name }}</a>
                    <form action="{{ route('folders.destroy', $folder) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 text-sm hover:underline">Удалить</button>
                    </form>
                </div>
            @endforeach
            @if($folders->isEmpty())
                <p class="text-gray-400 italic">Папок пока нет</p>
            @endif
        </div>

        <h2 class="font-bold text-xl mb-3 border-b pb-2">Файлы</h2>
        <div class="space-y-4">
            @foreach($files as $file)
                <div class="border p-4 rounded flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white shadow-sm">
                    
                    <div class="flex gap-4 items-center">
                        @if(Str::startsWith($file->mime_type, 'image/'))
                            <img src="{{ Storage::url($file->path) }}" class="w-16 h-16 object-cover rounded border">
                        @else
                            <div class="w-16 h-16 flex items-center justify-center bg-gray-100 rounded border text-2xl">📄</div>
                        @endif
                        
                        <div>
                            <p class="font-bold text-gray-800 break-all">{{ $file->name }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ round($file->size / 1024, 2) }} КБ
                                @if($file->tags) | <span class="text-indigo-500 font-bold">Теги: {{ $file->tags }}</span> @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-2 text-sm w-full md:w-auto">
                        <a href="{{ route('files.download', $file) }}" class="bg-blue-500 text-white px-3 py-1.5 rounded hover:bg-blue-600">Скачать</a>
                        
                        <form action="{{ route('files.rename', $file) }}" method="POST" class="flex">
                            @csrf @method('PATCH')
                            <input type="text" name="name" value="{{ $file->name }}" class="border p-1 rounded-l text-sm w-32">
                            <button type="submit" class="bg-gray-200 px-2 rounded-r hover:bg-gray-300" title="Переименовать">✎</button>
                        </form>

                        <form action="{{ route('files.move', $file) }}" method="POST" class="flex">
                            @csrf @method('PATCH')
                            <select name="new_folder_id" class="border p-1 text-sm rounded-l max-w-[120px]">
                                <option value="">В корень</option>
                                @foreach(App\Models\Folder::where('user_id', auth()->id())->get() as $f)
                                    <option value="{{ $f->id }}">В {{ $f->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="bg-gray-200 px-2 rounded-r hover:bg-gray-300" title="Перенести">➔</button>
                        </form>

                        <form action="{{ route('files.destroy', $file) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="bg-red-500 text-white px-3 py-1.5 rounded hover:bg-red-600">Удалить</button>
                        </form>
                    </div>
                </div>
            @endforeach
            @if($files->isEmpty())
                <p class="text-gray-400 italic">Файлов пока нет</p>
            @endif
        </div>
    </div>
</body>
</html>