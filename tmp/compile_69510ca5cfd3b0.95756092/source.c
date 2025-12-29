#include <stdio.h>

int multiply(int a, int b) {
    return a * b;
}

float calculateAverage(int arr[], int size) {
    int sum = 0;
    for (int i = 0; i < size; i++) {
        sum += arr[i];
    }
    return (float)sum / size;
}

int main() {
    int x = 5, y = 7;
    int product = multiply(x, y);
    printf("Product: %d\n", product);
    
    int numbers[] = {10, 20, 30, 40, 50};
    float average = calculateAverage(numbers, 5);
    printf("Average: %.2f\n", average);
    
    return 0;
}