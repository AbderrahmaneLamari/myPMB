<template>
    <div>
        <div class="wysiwyg-tree-item"><b>{{ blockLabels[tree.type] }}</b></div>
        <ul>
            <li v-for="(child, index) in tree.blocks" :key="index">
                <tree :tree="child" :blockLabels="blockLabels"></tree>
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        name: "tree",
        props: ["tree", "blockLabels"],
    };
</script>