<form method="POST" action="{{ route('admin.logout') }}" id="logout-form">
    @csrf
    <button type="submit"
            class="dropdown-item"
            onclick="
                if(window.autoSaveInterval) {
                    clearInterval(window.autoSaveInterval);
                    window.autoSaveInterval = null;
                }
                window.isLoggingOut = true;
            ">
        Sign Out
    </button>
</form>