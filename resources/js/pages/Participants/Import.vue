<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const props = defineProps<{
  users: {
    data: any[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
  filters: {
    search: string;
  };
}>();

const searchQuery = ref(props.filters.search ?? '');
const importingUser = ref(false);
const savingIds = ref<number[]>([]);
const importForm = ref({ username: '', role: 'participant', can_login: true });

const isSaving = (id: number) => savingIds.value.includes(id);

function submitImport() {
  if (!importForm.value.username.trim()) return;

  importingUser.value = true;

  router.post(
    route('participants.import'),
    {
      username: importForm.value.username,
      role: importForm.value.role,
      can_login: importForm.value.can_login,
    },
    {
      preserveScroll: true,
      onFinish: () => {
        importingUser.value = false;
      },
      onSuccess: () => {
        importForm.value = { username: '', role: 'participant', can_login: true };
      },
    },
  );
}

function saveUser(user: any) {
  if (isSaving(user.id)) return;

  savingIds.value.push(user.id);

  router.patch(
    route('participants.import.update', user.id),
    {
      role: user.role,
      can_login: Boolean(user.can_login),
      name: user.name,
      firstname: user.firstname,
    },
    {
      preserveScroll: true,
      onFinish: () => {
        savingIds.value = savingIds.value.filter((id) => id !== user.id);
      },
    },
  );
}

function updateSearch() {
  router.get(
    route('participants.import.page'),
    { search: searchQuery.value || undefined },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    },
  );
}
</script>

<template>
  <AppLayout>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      <Card>
        <CardHeader>
          <CardTitle>Benutzer importieren</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid w-full gap-3 sm:grid-cols-[minmax(240px,1fr)_140px_auto_auto] sm:items-end">
            <div>
              <label class="mb-1 block text-xs text-gray-600" for="import-username">Benutzername</label>
              <input
                id="import-username"
                v-model="importForm.username"
                type="text"
                placeholder="z. B. max.mustermann"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="mb-1 block text-xs text-gray-600" for="import-role">Rolle</label>
              <select
                id="import-role"
                v-model="importForm.role"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
              >
                <option value="participant">participant</option>
                <option value="teacher">teacher</option>
              </select>
            </div>
            <div class="mb-2 flex items-center gap-3 text-sm text-gray-700">
              <button
                type="button"
                role="switch"
                :aria-checked="importForm.can_login"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition"
                :class="importForm.can_login ? 'bg-green-600' : 'bg-gray-300'"
                @click="importForm.can_login = !importForm.can_login"
              >
                <span
                  class="inline-block h-5 w-5 transform rounded-full bg-white transition"
                  :class="importForm.can_login ? 'translate-x-5' : 'translate-x-1'"
                />
              </button>
              Login erlauben
            </div>
            <button
              type="button"
              class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="importingUser"
              @click="submitImport"
            >
              {{ importingUser ? 'Importiere…' : 'Importieren' }}
            </button>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <CardTitle>Importierte Benutzer bearbeiten</CardTitle>
            <div class="w-full sm:w-72">
              <label class="sr-only" for="user-search">Benutzer suchen</label>
              <input
                id="user-search"
                v-model="searchQuery"
                type="search"
                placeholder="Benutzer suchen"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                @input="updateSearch"
              />
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Username</TableHead>
                <TableHead>Name</TableHead>
                <TableHead>Vorname</TableHead>
                <TableHead>Rolle</TableHead>
                <TableHead>Login</TableHead>
                <TableHead class="text-right">Aktion</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="users.data.length === 0">
                <TableCell colspan="6" class="py-4 text-center text-gray-500">Keine Benutzer gefunden.</TableCell>
              </TableRow>
              <TableRow v-for="user in users.data" :key="user.id">
                <TableCell>{{ user.username }}</TableCell>
                <TableCell>
                  <input
                    v-model="user.name"
                    type="text"
                    class="w-full rounded-md border border-gray-300 px-2 py-1 text-sm"
                  />
                </TableCell>
                <TableCell>
                  <input
                    v-model="user.firstname"
                    type="text"
                    class="w-full rounded-md border border-gray-300 px-2 py-1 text-sm"
                  />
                </TableCell>
                <TableCell>
                  <select v-model="user.role" class="rounded-md border border-gray-300 px-2 py-1 text-sm">
                    <option value="participant">participant</option>
                    <option value="teacher">teacher</option>
                  </select>
                </TableCell>
                <TableCell>
                  <button
                    type="button"
                    role="switch"
                    :aria-checked="Boolean(user.can_login)"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition"
                    :class="Boolean(user.can_login) ? 'bg-green-600' : 'bg-gray-300'"
                    @click="user.can_login = !Boolean(user.can_login)"
                  >
                    <span
                      class="inline-block h-5 w-5 transform rounded-full bg-white transition"
                      :class="Boolean(user.can_login) ? 'translate-x-5' : 'translate-x-1'"
                    />
                  </button>
                </TableCell>
                <TableCell class="text-right">
                  <button
                    type="button"
                    class="rounded-md border border-gray-300 bg-white px-3 py-1 text-sm font-medium hover:bg-gray-50 disabled:opacity-60"
                    :disabled="isSaving(user.id)"
                    @click="saveUser(user)"
                  >
                    {{ isSaving(user.id) ? 'Speichert…' : 'Speichern' }}
                  </button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <div v-if="users.links.length > 3" class="mt-6 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <template v-for="(link, key) in users.links" :key="key">
                <component
                  :is="link.url ? Link : 'span'"
                  v-bind="link.url ? { href: link.url } : {}"
                  class="relative inline-flex items-center border px-4 py-2 text-sm font-medium"
                  :class="{
                    'z-10 border-indigo-500 bg-indigo-50 text-indigo-600': link.active,
                    'border-gray-300 bg-white text-gray-500 hover:bg-gray-50': !link.active,
                    'rounded-l-md': key === 0,
                    'rounded-r-md': key === users.links.length - 1,
                    'cursor-default text-gray-400': !link.url,
                  }"
                  :aria-disabled="!link.url"
                >
                  <span v-html="link.label" />
                </component>
              </template>
            </nav>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
