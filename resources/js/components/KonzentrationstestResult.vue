<script setup lang="ts">
const props = defineProps<{
  results: any;
}>();

const page1Correct = ['13', '26', '133', '39', '125', '50', '480', '210', '11,25', '242']
const page2Correct = ['3', '1', '3', '2', '2', '1', '2', '4', '1', '3']
const page3Correct = ['7', '1', '4', '3', '1', '3', '3', '0', '2', '2', '5', '2', '3', '2', '2', '4', '3', '3', '1', '3', '3', '0', '2']
const page4Correct = ['12', '11', '13', '10', '11', '9', '16', '14', '13', '10', '15', '15', '12', '13']
const page5Correct = ['7', '4', '8', '5', '7', '9', '7', '10', '10', '9']

</script>

<template>
  <div class="p-6 bg-background border rounded-lg">
    <h2 class="text-xl font-semibold mb-4">628 Results</h2>
    <div class="mb-6 w-full max-w-md">
      <table class="w-full text-sm border rounded-lg overflow-hidden shadow">
        <tbody>
          <tr class="bg-muted/40">
            <td class="font-semibold px-3 py-2 w-1/2">Wrong Answers</td>
            <td class="px-3 py-2">{{ results.wrong_count }}</td>
          </tr>
          <tr>
            <td class="font-semibold px-3 py-2">Performance Category</td>
            <td class="px-3 py-2">{{ results.performance_category.category }} ({{ results.performance_category.range }})</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div>
      <h3 class="font-bold mb-2">Detailed Answers</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm border rounded-lg shadow">
          <thead class="bg-muted/40">
            <tr>
              <th class="px-2 py-1 text-left font-semibold">Page</th>
              <th class="px-2 py-1 text-left font-semibold">Question #</th>
              <th class="px-2 py-1 text-left font-semibold">Your Answer</th>
              <th class="px-2 py-1 text-left font-semibold">Correct Answer</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(answer, idx) in results.page1" :key="`p1-${idx}`">
              <tr>
                <td class="px-2 py-1">1</td>
                <td class="px-2 py-1">{{ idx + 1 }}</td>
                <td class="px-2 py-1">{{ answer }}</td>
                <td class="px-2 py-1">{{ page1Correct[idx] }}</td>
              </tr>
            </template>
            <template v-for="(answer, idx) in results.page2" :key="`p2-${idx}`">
              <tr>
                <td class="px-2 py-1">2</td>
                <td class="px-2 py-1">{{ idx + 1 }}</td>
                <td class="px-2 py-1">{{ answer }}</td>
                <td class="px-2 py-1">{{ page2Correct[idx] }}</td>
              </tr>
            </template>
            <template v-for="(answer, idx) in results.page3" :key="`p3-${idx}`">
              <tr>
                <td class="px-2 py-1">3</td>
                <td class="px-2 py-1">{{ idx + 1 }}</td>
                <td class="px-2 py-1">{{ answer }}</td>
                <td class="px-2 py-1">{{ page3Correct[idx] }}</td>
              </tr>
            </template>
            <template v-for="(answer, idx) in results.page4" :key="`p4-${idx}`">
              <tr>
                <td class="px-2 py-1">4</td>
                <td class="px-2 py-1">{{ idx + 1 }}</td>
                <td class="px-2 py-1">{{ answer }}</td>
                <td class="px-2 py-1">{{ page4Correct[idx] }}</td>
              </tr>
            </template>
            <template v-for="(answer, idx) in results.page5" :key="`p5-${idx}`">
              <tr>
                <td class="px-2 py-1">5</td>
                <td class="px-2 py-1">{{ idx + 1 }}</td>
                <td class="px-2 py-1">{{ answer }}</td>
                <td class="px-2 py-1">{{ page5Correct[idx] }}</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
